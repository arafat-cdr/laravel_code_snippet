### Laravel Image Upload Example

### Laravel Config/filesystems.php Look Like This

```php

'disk' => [

	'public' => [
	    'driver' => 'local',
	    'root' => storage_path('app/public'),
	    'url' => env('APP_URL').'/storage',
	    'visibility' => 'public',
	],

],


```

### For Laravel Image Upload Code 
```php

if ($request->hasFile('img')) {

    $image       = $request->file('img');
    $ext         = $image->getClientOriginalExtension();

    $filename = round(microtime(true)).'.'.$ext;

    # move the file to the folder

    $image->move(storage_path("app/public/editorial-board-img/"), $filename);


    $upload_img_name = "storage/editorial-board-img/$filename";

    $data['img']        = $upload_img_name;
}



```



### Laravel Send Mail Configuration
```bash
MAIL_DRIVER=sendmail
MAIL_HOST=smtp.gmail.com
MAIL_PORT=25
MAIL_USERNAME=mymail@gamil.com
MAIL_PASSWORD=my_password
MAIL_ENCRYPTION=tls
```
