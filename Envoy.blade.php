{{--
    Laravel Envoy deployment script — HW-21.

    One-command deploy:   vendor/bin/envoy run deploy
    Dry run (no SSH/exec): vendor/bin/envoy run deploy --pretend

    The story "deploy" runs an in-place atomic-symlink style release flow.
    The first task is a TEST GATE that runs locally before anything touches
    the production host: pint (style), phpstan (static analysis) and the test
    suite. If any of them fail, Envoy aborts with a non-zero exit code and the
    rest of the story never runs — so low-quality code can never reach prod.

    Configuration is taken from environment variables so no real host is
    hard-coded. Override per deploy, e.g.:
        DEPLOY_HOST=deploy@1.2.3.4 DEPLOY_BRANCH=main vendor/bin/envoy run deploy
--}}

@setup
    // ---- Deploy configuration (override via env, never hard-code real hosts) ----
    $host    = getenv('DEPLOY_HOST')   ?: 'deploy@your-prod-host';   // ssh target
    $repo    = getenv('DEPLOY_REPO')   ?: 'git@github.com:your-org/otus.git';
    $branch  = getenv('DEPLOY_BRANCH') ?: 'main';                    // branch or tag
    $baseDir = getenv('DEPLOY_PATH')   ?: '/var/www/otus';           // deploy root on prod

    // Atomic releases layout:
    //   {baseDir}/repo            -> bare-ish working clone we fetch into
    //   {baseDir}/releases/<ts>   -> a fresh checkout per deploy
    //   {baseDir}/current         -> symlink to the active release
    //   {baseDir}/shared          -> persistent .env, storage, etc.
    $releaseId  = date('YmdHis');
    $releaseDir = $baseDir.'/releases/'.$releaseId;
    $current    = $baseDir.'/current';
    $shared     = $baseDir.'/shared';
@endsetup

@servers(['local' => '127.0.0.1', 'web' => $host])

{{-- Full pipeline. Test gate runs LOCALLY first, then we touch the server. --}}
@story('deploy')
    test-gate
    pull
    dependencies
    shared-links
    migrate
    optimize
    queue-restart
    activate
@endstory

{{--
    QUALITY / TEST GATE — runs on the local machine (the box invoking Envoy,
    typically CI). Any non-zero exit aborts the whole story before deploy.
--}}
@task('test-gate', ['on' => 'local'])
    echo '==> Quality gate: pint --test'
    vendor/bin/pint --test
    echo '==> Quality gate: phpstan'
    vendor/bin/phpstan analyse --memory-limit=512M --no-progress
    echo '==> Quality gate: php artisan test'
    php artisan test
    echo '==> Quality gate passed; proceeding to deploy.'
@endtask

{{-- Fetch the requested branch/tag into a fresh release directory on prod. --}}
@task('pull', ['on' => 'web'])
    set -e
    echo '==> Preparing release {{ $releaseId }}'
    mkdir -p {{ $baseDir }}/releases {{ $shared }}
    if [ ! -d {{ $baseDir }}/repo/.git ]; then
        git clone {{ $repo }} {{ $baseDir }}/repo
    fi
    cd {{ $baseDir }}/repo
    git fetch --all --prune --tags
    git checkout --force {{ $branch }}
    git reset --hard origin/{{ $branch }} 2>/dev/null || git reset --hard {{ $branch }}
    rm -rf {{ $releaseDir }}
    mkdir -p {{ $releaseDir }}
    git --work-tree={{ $releaseDir }} checkout -f {{ $branch }}
@endtask

{{-- Production dependencies only — no dev tooling on prod. --}}
@task('dependencies', ['on' => 'web'])
    set -e
    cd {{ $releaseDir }}
    composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
@endtask

{{-- Wire the release to shared, persistent resources (.env, storage). --}}
@task('shared-links', ['on' => 'web'])
    set -e
    cd {{ $releaseDir }}
    ln -nfs {{ $shared }}/.env {{ $releaseDir }}/.env
    rm -rf {{ $releaseDir }}/storage
    ln -nfs {{ $shared }}/storage {{ $releaseDir }}/storage
@endtask

{{-- Run migrations against prod DB (non-interactive). --}}
@task('migrate', ['on' => 'web'])
    set -e
    cd {{ $releaseDir }}
    php artisan migrate --force
@endtask

{{-- Cache config/routes/views for production performance. --}}
@task('optimize', ['on' => 'web'])
    set -e
    cd {{ $releaseDir }}
    php artisan optimize:clear
    php artisan optimize
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
@endtask

{{-- Gracefully restart queue workers so they pick up the new code. --}}
@task('queue-restart', ['on' => 'web'])
    set -e
    cd {{ $releaseDir }}
    php artisan queue:restart
@endtask

{{-- Atomic switch: flip the `current` symlink to the new release. --}}
@task('activate', ['on' => 'web'])
    set -e
    ln -nfs {{ $releaseDir }} {{ $current }}
    echo '==> current -> {{ $releaseDir }}'
    # Keep the 5 most recent releases, prune the rest.
    cd {{ $baseDir }}/releases && ls -1dt */ | tail -n +6 | xargs -r rm -rf
@endtask

@finished
    echo "✅ Deploy finished: release {$releaseId} is now live.\n";
@endfinished
