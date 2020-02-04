# dicoding-azure-final
Final submission for "Menjadi Azure Cloud Developer" class of Dicoding

# Usage

- Clone repository
- Place repository folder in apache/nginx server directory
- Download composer.phar, place it in this repository directory
- `php composer.phar install`
- Create .env file, fill with this:
```
ACCOUNT_NAME=<your_account_name>
ACCOUNT_KEY=<your_account_key>
BLOB_CONTAINER_URL=<your_blob_container_url>
SUBSCRIPTION_KEY=<your_subscription_key>
```
- Open index.php via browser. Example url: `http://localhost/dicoding-azure-final/index.php`