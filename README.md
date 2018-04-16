# CiviApi3

This is a quick fork/rewrite of CiviCRM API3 package https://github.com/donquixote/CiviRemotePHP as a Laravel package. Thanks @donquixote ...

I have made several changes with regards to the use of Json query strings... in fact, they are not used here... I never had good luck with them... maybe someone can make some suggestions, but looking at the source, Json is not a first class citizen with this API...


## Install

*Via Composer*

``` bash
composer require leanwebstart/civi-api3
```

### Publish the configuration
Then you will need to run vendor publish to create the configuration file. 

``` bash
$ php artisan vendor:publish
```

This will create CiviAPI3.php in the config directory. You will need to update the configuration... You shoud set the values in your .env file. Here are the configuration values...

#### Host
Where your host is... this is without the actual path to civi as it varries depending on the integration.
```
CIVI_HOST=http://example.com
```
#### Integration
What your CiviCRM is integrated into... can be wordpress, drupal or joomla ... defaults to wordpress if you omit it
Note:
You can change the path to civi in the CiviAPI3.php config file... I use wordpress, I put what I could find as paths to the other environment... you might need to adjust them... 
```
CIVI_INTEGRATION=wordpress
```
### Api Keys...
You can find the documentation here... https://docs.civicrm.org/dev/en/latest/api/interfaces/#keys 
However, it is rather skim... These are 32 character strings. Keep the characters uri safe... I like to keep it to numbers and letters... Just make it complicated. Here is how to set them up...

#### CiviCRM site key
This one is set in your civicrm.settings.php file. You will need to edit the line
```php
define( 'CIVICRM_SITE_KEY', '47b8556a24bbe7cd49fd512263469c79');
```
with your key... this is what you set-up in the CIVI_SITE_KEY in your .env file.
```
CIVI_SITE_KEY=47b8556a24bbe7cd49fd512263469c79
```
#### User's API key
The key that is set for the user that is authenticated for api access. You will need to create this one directly in the database. There is no external tool (An extension exists... but why burden your install for just one key...). Basically, you will set the key on the contact you want to use. I create an API contact and then simply use it... 
You can create it with something like that... 
```sql
UPDATE civicrm_contact
SET api_key = 'bdd90e5627IGwgsByKL9s8gw54G3uj35'
WHERE id = the id of your contact... 
```
Then you transpose that key in the CIVI_USER_KEY setting in your .env file... 
```
CIVI_USER_KEY=bdd90e5627IGwgsByKL9s8gw54G3uj35
```
### So just append those lines to your .env file

```
CIVI_HOST=http://example.com
CIVI_INTEGRATION=wordpress
CIVI_SITE_KEY=47b8556a24bbe7cd49fd512263469c79
CIVI_USER_KEY=bdd90e5627IGwgsByKL9s8gw54G3uj35
```

## Usage

``` php
    // Get contact with ID 230
    $api = new Leanwebstart\CiviApi3\CiviApi();
    $result = $api->Contact->Get(230);
```

This package uses the query string/POST structure... You must format your parameters properly so they translate into the proper query string. It would be nice to have a query builder that is eloquent style, but I don't have the time right now...

So for example:

```php
$result = $api->Membership->Get(array("sequential" => 1, "join_date[>=]" => "2018-01-01", "options[limit]" => 10 ));
```

Calls the api with the proper structure... That is... Field name strings include the condition in the square brackets, This translates into the proper query string format in the $api object... 

```html
....&sequential=1&join_date[>=]=2018-01-01&options[limit]=10
```

Now, the package will pass parameters as a url encoded POST Form (content type = application/x-www-form-urlencoded) ... so [>=] is perfectly allright... 

So if you want everything from January first 2018 on, pass in the proper format... 

`"join_date[>=]" => "2018-01-01"`

Also since the condition defaults to = then you should not include it if only equal is required... actually, the API will just ignore the condition if you include [=] and not return an error... so be ware...So if you want only january 1st 2018, then enter it like that:

`"join_date" => "2018-01-01"`

 Now this is true for the conditions that require a single element, but conditions that require multiple elements like BETWEEN and IN/NOT IN ... require a different syntax... To get all ids between 1 and 100 for example...:

 `"id[BETWEEN][]" => 1, "id[BETWEEN][]" => 100`

Same would be true for IN ... but one entry for each value... :) So to get WHERE ID IN(1, 4, 40) ... 

`"id[IN][]" => 1, "id[IN][]" => 4, "id[IN][]" => 40`

Now a special case that is handled by the api call... if you don't want to pass any value, such as when chaining api calls you can set the value of the array item to null... That is because the keys make it to the attribute name in the query string...so if you provide no value, you will start to have numeric keys that will break your query string... So to chain in the contacts for example... 

`api.Contact.Get[values] => null`

Will be translated correctly into the `...&api.Contact.Get[values]&...` in the query string.

Note that there is also a shorthand if all you want is an entity of a known id... just pass an int

`$result = api.Contact.Get(124);`

Will get the contact with the id=124 (that is the internal id/primary key).

### Quick test

Want a quick test... try adding this route to your web.php file. Don't forget to adjust the date!

```php
Route::get('/testapi', function(){

    // You need an instance of the api
    $api = new Leanwebstart\CiviApi3\CiviApi();
  
    //The parameter names are verbatim, make sure to include the comparators unless you mean = as 
    //equal is the default... Note how the chained api calls are made with a null value...
    $result = $api->Contribution->Get(
          array(
            "sequential" => 1,
            "receive_date[>=]" => "2018-04-15",
            "options[limit]" => 10,
            "contribution_status_id" => 1,
            "api.Contact.Get[values]" => null,
            "api.LineItem.Get[values]" => null ));
    
    // This results contains the first 10 Contributions from the given date... 
    // We chain the Contact and the line items of the Contribution... so you have a complete picture
    // of that transaction.
    foreach( $result->values as $contribution){
        echo "Contribution<br>";
        var_dump($contribution);
        if( !empty($contribution->api_Contact_Get->count)){
            echo "Contact<br>";
            var_dump($contribution->api_Contact_Get->values);
        }else {
            echo "No Contact<br>";
        }

        if( !empty($contribution->api_LineItem_Get->count)){
            echo "Line Items<br>";
            var_dump($contribution->api_LineItem_Get->values);
        }else {
            echo "No Line Items<br>";
        }
    }
      
});
```

## Unlicense

Released under the **Unlicense**. Please see [Unlicense File](UNLICENSE.md) for more information.

