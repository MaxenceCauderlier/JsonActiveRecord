# JsonActiveRecord
A PHP class to deal with JSON like Active Record

## Requirements

- PHP >= 7.4

## Install

```bash
composer require maxkoder/json-active-record
```

## Usage

** See test.php for examples **

### Simple class

```php
class User extends JsonActiveRecord
{
    protected static string $primaryKey = 'user_id';

    protected static string $filePath = 'users.json';

    public function posts(): array
    {
        // Will return posts's user (array of Post objects)
        // based on author_user_id foreign key (from posts)
        // third parameter is local key, default to $primaryKey
        return $this->hasMany(Post::class, 'author_user_id');
    }
}
```

You have to create a class that extends JsonActiveRecord
users.json (the path to the file) will be used to store data, it will be created automatically

### Primary Key

If the primary key is different from 'id', you have to set up in the class
```php
protected static string $primaryKey = 'user_id';
```

Primary key is automatically incremented if not provided :

```php
$user = (new User(['name' => 'John Doe', 'email' => 'john@example.com']))->save();
echo $user->user_id; // 1

### Save / Update

When saving datas, if the primary key is not provided, it will be automatically generated
If provided, datas will be updated :

```php
$user = (new User(['name' => 'John Doe', 'email' => 'john@example.com']))->save();
$user->name = 'Johnny Boy';
$user->save();
echo $user->name; // Johnny Boy
```

#### Temporary attributes

You can also set temporary attributes that will not be saved. Attributes name start with "_" :

```php
$user = (new User(['name' => 'John Doe', 'email' => 'john@example.com']))->save();
$user->_tempPassword = 'secret123';
$user->save();
echo $user->_tempPassword; // null
```

### Delete

```php
$user = (new User(['name' => 'John Doe', 'email' => 'john@example.com']))->save();
$user->delete();
```

### Query Builder

```php
$users = User::queryBuilder()
    ->where('active', '=', 1)
    ->get();
// return array of User objects
```

### Get all records

```php
$users = User::all();
// return array of User objects
```

### Get first/last record

```php
(new User(['name' => 'John Doe', 'email' => 'john@example.com']))->save();
(new User(['name' => 'Jane Doe', 'email' => 'jane@example.com']))->save();
(new User(['name' => 'Johnny Boy', 'email' => 'johnny@example.com']))->save();

echo User::first()->name; // John Doe
echo User::last()->name; // Johnny Boy
```

### Special methods

```php
echo User::findPK(1)->name; // = User::find('user_id', 1)->name
```
