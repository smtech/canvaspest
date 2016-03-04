# CanvasPest

Object-oriented access to the Canvas API using PHP.

## Install

In your `composer.json`, include:

```PHP
"require": {
	"smtech/canvaspest": "1.*"
}
```

## Use

### CanvasPest

Create a new CanvasPest to make RESTful queries to the Canvas API:

```
// construct with the API URL and an API access token
$api = new CanvasPest('https://canvas.instructure.com/api/v1', 'df2bcbad95f606d6e80093f8e40c4e5ca171d8c5e4f2138e1d58273e33b262ef')
```

Make a RESTful query to the API:

```PHP
// GET, PUT, POST, DELETE are all supported
$obj = $api->get('users/self/profile');
```

The response from a query is either a `CanvasObject` or a `CanvasArray` (of CanvasObjects, natch), depending on whether you requested a specific object or a list of objects (even if the list turns out to be a single object).

### CanvasObjects

CanvasObject fields can be accessed either object-style or array style:

```PHP
$obj = $api->get('courses/123');
echo $obj['sis_course_id']; // array-style
echo $obj->title; // object-style
```

### CanvasArrays

CanvasArrays can be iterated conveniently using the `foreach` control structure.

```PHP
$arr = $api->get('/accounts/1/users');
foreach($arr as $obj) {
	echo $obj->name;
}
```

One could also access arbitrary elements of the CanvasArray:

```PHP
$arr = $api->get('accounts/1/courses');
echo $arr[1337]->title;
```

Note that both CanvasObjects and CanvasArrays are immutable objects -- that is, they are treated as read-only. In fact, if you attempt to alter a CanvasObject or a CanvasArray, exceptions will be thrown.
