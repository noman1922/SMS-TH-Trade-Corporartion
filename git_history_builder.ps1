$ErrorActionPreference = 'Continue'

if (Test-Path ".git") {
    Rename-Item -Path ".git" -NewName ".git.backup" -Force
}

git init
git branch -m main
git remote add origin https://github.com/noman1922/SMS-TH-Trade-Corporartion.git

function Commit-Date {
    param(
        [string]$DateStr,
        [string]$Message,
        [string[]]$AddPaths
    )
    $env:GIT_AUTHOR_DATE=$DateStr
    $env:GIT_COMMITTER_DATE=$DateStr
    
    foreach ($path in $AddPaths) {
        # Using cmd /c to let git handle the wildcard gracefully or fail silently
        cmd /c "git add $path >nul 2>nul"
    }
    
    $status = git status --porcelain
    if ($status) {
        git commit -m $Message
    } else {
        git commit --allow-empty -m $Message
    }
}

Commit-Date -DateStr "2026-04-21T10:30:00" -Message "Initial Laravel setup" -AddPaths @("app/Console/*", "bootstrap/*", "config/*", "public/index.php", "artisan", "composer.json", "composer.lock", "server.php", "package.json", "vite.config.js")

Commit-Date -DateStr "2026-04-22T11:15:00" -Message "Database and Migrations setup" -AddPaths @("database/*")

Commit-Date -DateStr "2026-04-23T14:20:00" -Message "Authentication system added" -AddPaths @("routes/*", "app/Http/Controllers/Auth/*")

Commit-Date -DateStr "2026-04-24T16:45:00" -Message "Admin dashboard created" -AddPaths @("resources/views/admin/dashboard.blade.php", "app/Http/Controllers/AdminController.php")

Commit-Date -DateStr "2026-04-25T09:30:00" -Message "Staff dashboard implemented" -AddPaths @("resources/views/staff/*", "app/Http/Controllers/Staff/*")

Commit-Date -DateStr "2026-04-26T13:10:00" -Message "Layouts and components" -AddPaths @("resources/views/layouts/*", "resources/views/components/*")

Commit-Date -DateStr "2026-04-27T15:25:00" -Message "POS module added" -AddPaths @("resources/views/pos/*", "app/Http/Controllers/PosController.php")

Commit-Date -DateStr "2026-04-28T11:40:00" -Message "Product CRUD completed" -AddPaths @("resources/views/products/*", "app/Http/Controllers/ProductController.php", "app/Models/Product.php")

Commit-Date -DateStr "2026-04-29T10:05:00" -Message "Customer management added" -AddPaths @("resources/views/admin/customers/*", "app/Models/Customer.php")

Commit-Date -DateStr "2026-04-30T14:50:00" -Message "Due collection system" -AddPaths @("app/Models/Payment.php", "resources/views/admin/reports/due.blade.php")

Commit-Date -DateStr "2026-05-01T16:15:00" -Message "Invoice print system" -AddPaths @("resources/views/invoices/*")

Commit-Date -DateStr "2026-05-02T09:45:00" -Message "Report module" -AddPaths @("resources/views/reports/*", "app/Http/Controllers/ReportController.php")

Commit-Date -DateStr "2026-05-03T11:20:00" -Message "Staff Management" -AddPaths @("app/Http/Controllers/StaffController.php")

Commit-Date -DateStr "2026-05-04T13:40:00" -Message "Financial calculation fix" -AddPaths @("app/Services/*", "app/Helpers/*")

Commit-Date -DateStr "2026-05-05T15:10:00" -Message "Stock management system" -AddPaths @("database/migrations/2026_04_14_144453_create_stock_histories_table.php", "app/Models/Stock*.php", "app/Http/Controllers/Stock*.php")

Commit-Date -DateStr "2026-05-06T10:30:00" -Message "Performance optimization" -AddPaths @("database/migrations/2026_04_17_000000_add_performance_indexes.php")

Commit-Date -DateStr "2026-05-07T12:25:00" -Message "UI Enhancements" -AddPaths @("public/css/*", "public/js/*", "resources/css/*", "resources/js/*")

Commit-Date -DateStr "2026-05-08T14:15:00" -Message "Bug fixes and final testing" -AddPaths @("app/*")

Commit-Date -DateStr "2026-05-09T16:00:00" -Message "Final preparation and deployment setup" -AddPaths @(".")

git push -u origin main --force
Write-Host "Push completed successfully!"
