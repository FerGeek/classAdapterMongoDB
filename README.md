# mongodb_adapter_drive
## Create connection
```php
include('MongoDB_.php');
//URI format: mongodb://<user>:<pwd>@<cluster_host>:<port>/<database>
$db = new MongoDB_('mongodb://localhost:27017/test');
```
### Alternative syntax
```php
//Option 1
$db = new MongoDB_(); // default -> 'mongodb://localhost:27017/test'

//Option 2 Manual URI (Recommended) 
$db = new MongoDB_('mongodb://localhost:27017/test');

//Option 3 (without credentials)
$db = new MongoDB_('localhost',27017); // Auto URI

//Option 4 (with credentials)
$db = new MongoDB_('localhost',27017,'user1','p4ssw0rd1','database_name'); // Auto URI

//Option 5 (with connection options)
$db = new MongoDB_('localhost',27017,'user1','p4ssw0rd1','database_name',$options); // Auto URI
```
## Collections
```php
// Create a collection
$db -> createCollection('name');
// Select a collection
$db -> selectCollection('name');
```
## CRUD
### Insert
```php
$db -> insert(['id_'=>123,'timestamp'=>time()],$options);
```
### Find
```php
$db -> find(['content'=>123],['limit'=>5,'skip'=>2]);
```
### update
```php 
$db -> update(['_id'=>123],['$set'=>['content'=>456]]);
```
### delete
```php
$db -> delete($where);
```
