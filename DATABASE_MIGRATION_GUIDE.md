# TinyURL Application - Database Migration and Storage Management

## Overview

This TinyURL application now supports multiple storage modes for handling different scales and requirements. The application has been completely refactored to use a modern, scalable architecture with the following key components:

## 🏗️ Architecture Components

### 1. **UrlStorageManager Service**
- **Location**: `app/Services/UrlStorageManager.php`
- **Purpose**: Centralized storage management with support for multiple storage modes
- **Features**:
  - Dynamic storage target selection
  - Caching support for improved performance
  - Unique token generation
  - Statistics collection across all storage modes

### 2. **Urls Model**
- **Location**: `app/Models/Urls.php`
- **Purpose**: Modern model with static methods for storage operations
- **Features**:
  - Static methods for CRUD operations
  - Validation and expiration handling
  - Backward compatibility with TinyUrl model

### 3. **Updated Controller**
- **Location**: `app/Http/Controllers/tinyUrlController.php`
- **Purpose**: Enhanced controller with comprehensive API endpoints
- **Features**:
  - Full CRUD operations
  - Error handling and logging
  - AJAX and traditional request support
  - URL management endpoints

## 📊 Storage Modes

### 1. **Single Table Mode** (`STORAGE_MODE=single`)
- **Description**: Traditional single table storage
- **Table**: `urls`
- **Use Case**: Small to medium applications
- **Pros**: Simple, easy to manage
- **Cons**: Limited scalability

### 2. **Multi-Table Mode** (`STORAGE_MODE=multi_table`)
- **Description**: Horizontal sharding across multiple tables
- **Tables**: 
  - `urls_a_f` (tokens starting with A-F)
  - `urls_g_l` (tokens starting with G-L)
  - `urls_m_r` (tokens starting with M-R)
  - `urls_s_z` (tokens starting with S-Z)
- **Use Case**: Medium to large applications
- **Pros**: Better performance, easier maintenance than multi-DB
- **Cons**: Still limited by single database

### 3. **Multi-Database Mode** (`STORAGE_MODE=multi_db`)
- **Description**: Horizontal sharding across multiple databases
- **Databases**: `mysql_1`, `mysql_2`, `mysql_3`, `mysql_4`
- **Use Case**: Large scale applications
- **Pros**: Maximum scalability, can distribute across servers
- **Cons**: Complex setup and maintenance

## 🔧 Configuration

### Environment Variables
```bash
# Storage mode selection
STORAGE_MODE=single  # Options: single, multi_table, multi_db

# Cache configuration
URL_CACHE_ENABLED=true
URL_CACHE_TTL=3600

# Token configuration
URL_TOKEN_LENGTH=8

# Multi-database configuration (only if STORAGE_MODE=multi_db)
DB_CONNECTION_1=mysql_1
DB_HOST_1=127.0.0.1
# ... (additional database connections)
```

### Configuration File
- **Location**: `config/tinyurl.php`
- **Purpose**: Centralized configuration for all TinyURL settings

## 🚀 API Endpoints

### Core Endpoints
- `POST /tiny-url` - Create shortened URL (AJAX compatible)
- `GET /{token}` - Redirect to original URL

### API Endpoints
- `GET /api/stats` - Get storage statistics
- `GET /api/storage-info` - Get storage mode information
- `GET /api/urls/{token}` - Get URL details
- `PUT /api/urls/{token}` - Update URL
- `DELETE /api/urls/{token}` - Delete URL
- `POST /api/urls/{token}/extend` - Extend URL expiration

## 🛠️ Management Commands

### Storage Management Command
```bash
# View statistics
php artisan tinyurl:storage stats

# Cleanup expired URLs
php artisan tinyurl:storage cleanup --force

# Migrate storage mode (not fully implemented)
php artisan tinyurl:storage migrate --mode=multi_table
```

## 📊 Database Migrations

### Available Migrations
1. `create_urls_table.php` - Single table mode
2. `create_sharded_url_tables.php` - Multi-table mode
3. `create_sharded_urls.php` - Multi-database mode

### Migration Usage
```bash
# Run all migrations
php artisan migrate

# Rollback specific migration
php artisan migrate:rollback
```

## 🎯 Key Features

### 1. **Intelligent Storage Selection**
- Automatic target selection based on token first character
- Load balancing across storage targets
- Failover handling

### 2. **Caching Layer**
- Redis/Memcached support for URL lookups
- Configurable TTL
- Cache invalidation on updates

### 3. **Token Generation**
- Alphanumeric tokens (A-Z, a-z, 0-9)
- Configurable length
- Collision detection and retry

### 4. **Comprehensive Logging**
- All operations logged with context
- Error tracking and debugging
- Performance monitoring

### 5. **Statistics and Monitoring**
- Real-time statistics across all storage modes
- Distribution tracking for sharded modes
- Performance metrics

## 🔄 Migration Process

### From Old to New System
1. **Backup existing data**
2. **Update environment variables**
3. **Run new migrations**
4. **Update application code**
5. **Test thoroughly**
6. **Deploy**

### Storage Mode Migration
1. **Set new storage mode in .env**
2. **Run appropriate migrations**
3. **Use migration command (when implemented)**
4. **Verify data integrity**

## 🏆 Performance Optimizations

### 1. **Database Level**
- Proper indexing on token and expired_at columns
- Connection pooling for multi-database mode
- Query optimization

### 2. **Application Level**
- Caching layer for frequent lookups
- Efficient token generation
- Batch operations for cleanup

### 3. **Infrastructure Level**
- Database server distribution (multi-DB mode)
- Load balancing
- Monitoring and alerting

## 🔒 Security Considerations

### 1. **Token Security**
- Sufficient entropy in token generation
- No predictable patterns
- Protection against enumeration attacks

### 2. **Input Validation**
- URL validation and sanitization
- XSS protection
- CSRF protection for all endpoints

### 3. **Access Control**
- Rate limiting on API endpoints
- Authentication for management operations
- Audit logging

## 📈 Scaling Recommendations

### Small Scale (< 1M URLs)
- Use `single` mode
- Single database server
- Basic caching

### Medium Scale (1M - 10M URLs)
- Use `multi_table` mode
- Enhanced caching strategy
- Database optimization

### Large Scale (> 10M URLs)
- Use `multi_db` mode
- Multiple database servers
- Advanced monitoring and alerting

## 🔧 Troubleshooting

### Common Issues
1. **Token collision errors** - Increase token length
2. **Cache miss issues** - Check cache configuration
3. **Database connection errors** - Verify connection settings
4. **Migration failures** - Check database permissions

### Debugging Commands
```bash
# Check storage statistics
php artisan tinyurl:storage stats

# View application logs
tail -f storage/logs/laravel.log

# Test database connections
php artisan tinker
>>> DB::connection('mysql_1')->select('SELECT 1')
```

## 📚 Additional Resources

- **Configuration Reference**: `config/tinyurl.php`
- **API Documentation**: Available endpoints in routes file
- **Migration Files**: `database/migrations/`
- **Service Classes**: `app/Services/`
- **Console Commands**: `app/Console/Commands/`

This architecture provides a solid foundation for scaling the TinyURL application from small personal projects to enterprise-level services.