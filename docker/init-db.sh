#!/usr/bin/env bash
set -e

echo "🔍 Initializing PostgreSQL database for Shami Restaurant..."

# Function to wait for database
wait_for_db() {
    echo "⏳ Waiting for PostgreSQL database to be ready..."
    local max_attempts=30
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'Connected'; } catch(Exception \$e) { echo 'Failed'; exit(1); }" 2>/dev/null | grep -q "Connected"; then
            echo "✅ Database connection established!"
            return 0
        fi
        
        echo "⏳ Attempt $attempt/$max_attempts - waiting for database..."
        sleep 2
        attempt=$((attempt + 1))
    done
    
    echo "❌ Failed to connect to database after $max_attempts attempts"
    exit 1
}

# Function to check if database is empty
is_database_empty() {
    local table_count=$(php artisan tinker --execute="
        try { 
            echo DB::select('SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = \\'public\\' AND table_type = \\'BASE TABLE\\'')[0]->count; 
        } catch(Exception \$e) { 
            echo '0'; 
        }
    " 2>/dev/null || echo "0")
    
    [ "$table_count" -eq 0 ]
}

# Function to run fresh migrations and seeders
run_fresh_setup() {
    echo "🚀 Running fresh migrations and seeders..."
    
    # Clear any cached data first
    php artisan config:clear || true
    php artisan cache:clear || true
    
    # Run fresh migrations with seeding
    echo "📊 Running fresh migrations..."
    php artisan migrate:fresh --force
    
    echo "🌱 Running database seeders..."
    php artisan db:seed --force --class=DatabaseSeeder
    
    echo "✅ Fresh database setup completed!"
}

# Function to run incremental migrations
run_incremental_setup() {
    echo "⚡ Running incremental migrations..."
    
    # Run pending migrations
    php artisan migrate --force
    
    # Check if we need to seed
    local user_count=$(php artisan tinker --execute="
        try { 
            echo \\App\\Models\\User::count(); 
        } catch(Exception \$e) { 
            echo '0'; 
        }
    " 2>/dev/null || echo "0")
    
    if [ "$user_count" -eq 0 ]; then
        echo "🌱 No users found - running seeders..."
        php artisan db:seed --force --class=DatabaseSeeder
        echo "✅ Database seeding completed!"
    else
        echo "👥 Found $user_count users - skipping seeding"
    fi
}

# Function to verify database setup
verify_setup() {
    echo "🔍 Verifying database setup..."
    
    php artisan tinker --execute="
        try {
            \$userCount = \\App\\Models\\User::count();
            \$tableCount = count(DB::select(\\\"SELECT tablename FROM pg_tables WHERE schemaname = 'public'\\\"));
            
            echo '✅ Database verification results:\\n';
            echo '   - PostgreSQL Version: ' . DB::select('SELECT version()')[0]->version . '\\n';
            echo '   - Total Tables: ' . \$tableCount . '\\n';
            echo '   - Total Users: ' . \$userCount . '\\n';
            
            if (\$userCount > 0 && \$tableCount > 0) {
                echo '   - Status: ✅ READY\\n';
                echo '\\n🎉 Database successfully initialized!\\n';
            } else {
                echo '   - Status: ❌ INCOMPLETE\\n';
                exit(1);
            }
        } catch(Exception \$e) {
            echo '❌ Database verification failed: ' . \$e->getMessage() . '\\n';
            exit(1);
        }
    " || {
        echo "❌ Database verification failed"
        exit 1
    }
}

# Main execution
main() {
    echo "🏪 Shami Restaurant - Database Initialization"
    echo "=============================================="
    
    # Wait for database connection
    wait_for_db
    
    # Check if database needs fresh setup or incremental updates
    if is_database_empty; then
        echo "🆕 Empty database detected - performing fresh setup"
        run_fresh_setup
    else
        echo "📋 Existing database detected - performing incremental setup"
        run_incremental_setup
    fi
    
    # Verify the setup
    verify_setup
    
    echo "🎯 Database initialization completed successfully!"
}

# Execute main function
main "$@"