## url: https://github.com/fw4-bvba/sweepbright-api

*** composer require fw4/sweepbright-api

```php
<?php

namespace App\Http\Controllers;

use App\MainImage;
use App\Models\Webhook;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use SweepBright\SweepBright;
use App\Models\Resource;

# web_lover test
use App\Models\AssetImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
# end web_lover test


class WebhookController extends Controller
{
    private $clientId = "1234";
    private $clientSecret = "client_secret_goes_here";

    public function handleWebhook(Request $request)
    {
        // dd( $_SERVER );

        $clientId = "1234";
        $clientSecret = "client_secret_goes_here";
        $estateId = $request->input("estate_id");
        $estateStatus = $request->input('event');
        // Check si les 2 variables sont true == non null
        if ($estateId && $estateStatus) {
            // Definis un nouvelle objet sweepbright pour le WEBBHOOK
            $client = new SweepBright();
            if (empty($accessToken) || $accessToken->hasExpired()) {
                $client->requestAccessToken($clientId, $clientSecret);
            } else {
                $client->setAccessToken($accessToken);
            }
            // On viens regarder quelle event le webhook a envoyer et on traite l'event en fonction du status pour eviter le retour 500 (ATTENTION PLUS DE DEBUG)
            if ($estateStatus === 'estate-deleted') {
                DB::table('webhook')->where('code', $estateId)->delete();

                # web_lover delete img supporting code
                $this->delete_img_on_crm_estate_delete( $estateId );
                # end web_lover delete img supporting code

            } elseif ($estateStatus === 'estate-added') {

                # web_lover add img on estate create
                $this->add_img_on_crm_estate_create( $estateId );
                # end web_lover add img on estate create

                $vilaine = new Webhook(['code' => $estateId,]);
                $vilaine->save();
                $client->setEstateUrl($estateId, "https://my_domain_name.com/webhook");
            } else {
                # web_lover Estate updated code 
                $this->update_img_on_crm_estate_update( $estateId );
                # End web_lover estate update code
                $client->setEstateUrl($estateId, "https://my_domain_name.com/webhook");
            }
        }
        // Fermeture rajoute du code si tu veut un rendus autre que une page blanche ou un redirect
    }
    public function callBack(Request $request)
    {
        return 'ok';
    }
    public function show(Request $request, $id)
    {
        $clientId = "1234";
        $clientSecret = "client_secret_goes_here";
        $client = new SweepBright();
        if (empty($accessToken) || $accessToken->hasExpired()) {
            $accessToken = $client->requestAccessToken($clientId, $clientSecret);
        } else {
            $client->setAccessToken($accessToken);
        }
        $estate = $client->getEstate($id);
        $estates[] = $estate;
        return view('details', compact('estates'));
    }

    public function Parse()
    {
        $images = MainImage::all();
        $clientId = "1234";
        $clientSecret = "client_secret_goes_here";
        $codes = Webhook::pluck('code')->toArray(); // récupère tous les codes en base et les met dans un tableau
        $estates = [];
        foreach ($codes as $code) {
            $client = new SweepBright();
            if (empty($accessToken) || $accessToken->hasExpired()) {
                $accessToken = $client->requestAccessToken($clientId, $clientSecret);
            } else {
                $client->setAccessToken($accessToken);
            }
            $estate = $client->getEstate($code);
            $estates[] = $estate;
        }
        return $estates;
    }

    # add by web_lover to get the estate obj
    public function get_estate_obj($estate_id)
    {

        $clientId = "1234";
        $clientSecret = "client_secret_goes_here";

        $client = new SweepBright();

        if (empty($accessToken) || $accessToken->hasExpired()) {
            $accessToken = $client->requestAccessToken($clientId, $clientSecret);
        } else {
            $client->setAccessToken($accessToken);
        }

        $estate = $client->getEstate($estate_id);

        // dd($estate);

        return $estate;
    }
    # End add by arafat to get estate obj

    # add by web_lover
    # date: Mar/13/2023
    # This function insert Img if img name is not there
    # This will download img to storage folder and save path
    # to database
    # This is needed for already used estate to this app
    # This will feed the data for all estate that img not yet
    # saved
    # Now when new create/update/delete happens it will do necessary action
    # to download img so this function now will not need or hardly needed

    public function feed_img_from_api_to_local_for_all_estate()
    {

        $estates = (new WebhookController)->Parse();

        # web_lover Testing

        // dd("web_lover data", $estates[8]);

        if ($estates) {
            foreach ($estates as $key => $estate) {

                $asset_id = $estate->id;
                # Now iterate img
                if ($estate->images) {
                    foreach ($estate->images as $kk => $image) {

                        $img_id = $image->id;
                        $img_name = $image->filename;
                        $img_url = $image->url;

                        # Check db if the img name already there
                        $is_img_already_saved = AssetImage::where('img_name', $img_name)->where('img_id', $img_id)->first();

                        # we are checked img not Saved
                        if (!$is_img_already_saved) {
                            # downlaod img
                            $response = Http::get($img_url);

                            if ($response->ok()) {
                                $imageData = $response->body();
                                $imageFileName = $img_name;

                                // dd($imageFileName);
                                // dump( $imageData );

                                Storage::disk('public')->put('asset_images/' . $imageFileName, $imageData);

                                $img_path = "asset_images/$imageFileName";

                                $dataArr = array(
                                    'property_id' => $asset_id,
                                    'img_id' => $img_id,
                                    'img_path' => $img_path,
                                    'img_name' => $img_name,
                                );

                                # save to the database
                                $res = AssetImage::create($dataArr);
                            }
                            # end download img


                        }
                    }
                }
            }
        }

        # end web_lover testing
    }

    # added by web_lover
    public static function get_save_img_using_id($asset_id)
    {
        $img = AssetImage::where('property_id', $asset_id)->get();

        return $img;
    }
    # End add by web_lover

    public function img_download_and_db_entry($estate)
    {
        if ($estate->images) {

            $asset_id = $estate->id;

            foreach ($estate->images as $kk => $image) {

                $img_id = $image->id;
                $img_name = $image->filename;
                $img_url = $image->url;

                # downlaod img
                $response = Http::get($img_url);
                $imageData = $response->body();
                $imageFileName = $img_name;

                // dd($imageFileName);
                // dump( $imageData );

                Storage::disk('public')->put('asset_images/' . $imageFileName, $imageData);

                $img_path = "asset_images/$imageFileName";

                $dataArr = array(
                    'property_id' => $asset_id,
                    'img_id' => $img_id,
                    'img_path' => $img_path,
                    'img_name' => $img_name,
                );

                # save to the database
                $res = AssetImage::create($dataArr);
            }
        }
    }

    # add by web_lover
    public function add_img_on_crm_estate_create($estate_id)
    {
        $estate = get_estate_obj($estate_id);
        img_download_and_db_entry($estate);
    }

    # web_lover img_delete funciton

    public function img_delete_from_storage_using_estate_id($estate_id)
    {
        # Delete img from storage folder using loop
        $all_old_images = AssetImage::where('property_id', $estate_id)->get();

        if ($all_old_images) {

            foreach ($all_old_images  as $kk => $image) {
                # Delete Img from storage Folder
                $path = $image->img_path;

                if (Storage::disk('public')->exists($path)) {

                    $res = Storage::disk('public')->delete($path);
                    // Image deleted successfully
                    if ($res) {
                        // echo 'img_deleted';
                    } else {
                        // echo 'img not able to delete';
                    }
                } else {
                    // echo 'img not found';
                }
            }
        } # End img delete using loop from storage folder
    }
    # web_lover

    public function update_img_on_crm_estate_update($estate_id)
    {
        # Delete all old img First delete from folder
        # Then delete from db
        $this->img_delete_from_storage_using_estate_id($estate_id);
        # Delete all img from database
        AssetImage::where('property_id', $estate_id)->delete();
        # end delete all img from database


        # Now do the update option
        # In update we will keep the img in storage folder
        # then keep it in db

        $estate = get_estate_obj($estate_id);
        img_download_and_db_entry($estate);
    }

    public function delete_img_on_crm_estate_delete($estate_id)
    {
        # delete img from storage folder
        $this->img_delete_from_storage_using_estate_id($estate_id);
        # end delete from storage folder

        # now delete img from database 
        AssetImage::where('property_id', $estate_id)->delete();
    }
    # end add by web_lover

}

// Routes Code

//    ..................WEBHOOK....................
Route::any('/webhook', [WebhookController::class, 'handleWebhook']);
Route::get('/estates/{estate}', [WebhookController::class, 'callBack'])->name('estates');
Route::put('/estates/{estate}/', [WebhookController::class, 'callBackDetail'])->name('detail');

# show details
Route::get('/details/{id}', [WebhookController::class, 'show'])->name('details');

// End Routes Code

```