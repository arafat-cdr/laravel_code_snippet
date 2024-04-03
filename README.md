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


public function googleScholarImgAdd(){

    # It contain only one img so if found img redirect
    # Use First For Checking if database has data or not
    $images = Option::where('option_name', 'google_scholar_module_img')->first();
    if( $images ){
        session()->flash('danger', 'Google Scholar Image Already Added . Delete This image To use a New Image');
        return redirect()->route("googleScholarImgList");
    }

    return view("backend.googleScholarLinkModule.googleScholarImgAdd");
}

public function googleScholarImgStore(Request $request){

    // dump($request);

    if ($request->hasFile('img')) {

        $image       = $request->file('img');
        $ext         = $image->getClientOriginalExtension();

        $filename = round(microtime(true)).'.'.$ext;

        # move the file to the folder

        $image->move(storage_path("app/public/img/"), $filename);


        $upload_img_name = "img/$filename";

        // dump( $upload_img_name);

        $res = Option::create(array(
            'option_name'   =>    'google_scholar_module_img',
            'option_value'  =>    $upload_img_name,
        ));

        if( $res ){
              session()->flash('success', 'Google Scholar Image Added Successfully');
          }else {
              
              session()->flash('danger', 'Google Scholar Image Added Failed');
          }

    }

    // $img_showing_url = URL::to('/').'/storage/app/public/'.$v->option_value;

    return redirect()->route("googleScholarImgList");

}

public function googleScholarImgDelete($id){

    $img = Option::find($id);

    if( $img ){

        $image_path = storage_path('app/public/'.$img->option_value);
        
        if(File::exists($image_path)) {
            File::delete($image_path);
        }

    }

    $res = Option::where("id", $id)->delete();

    if ($res) {
        session()->flash('success', 'Data Deleting Successfull');
    } else {

        session()->flash('danger', 'Data Deleting Failed');
    }

    return redirect()->route("googleScholoarLinkList");
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
