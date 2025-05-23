#!/bin/bash
set -e

# Wait for databases to be ready
sleep 30

# Import user service
mysql -h user-db -u root -proot user_service < /docker-entrypoint-initdb.d/user_service.sql
echo "Imported user service"

# Import product service
mysql -h product-db -u root -proot product_service < /docker-entrypoint-initdb.d/product_service.sql
echo "Imported product service"

# Import order service
mysql -h order-db -u root -proot order_service < /docker-entrypoint-initdb.d/order_service.sql
echo "Imported order service"

# Import content service
mysql -h content-db -u root -proot content_service < /docker-entrypoint-initdb.d/content_service.sql
echo "Imported content service"

echo "All imports completed"
