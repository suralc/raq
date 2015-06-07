# Raq


Raq (**R**equest **a**nd **Q**uery) aims to be a small convenience wrapper above guzzle and the symfony dom crawler 
to retrieve content from remote sites. It utilizes guzzles async functionality.

## Examples
Simple use: 

```php
// use function Raq\fetch; // uncomment on PHP 5.6+
// $responseHandle wraps a Guzzle promise
$responseHandle = \Raq\fetch('http://my.url');
// the promise will not be resolved until you call the query method on wrapper
$result = $responseHandle->query(function(QueryBuilder $q, ResponseInterface $r) {
	// retrieve all a tags that are children of div tags
	return $q->createXmlQuery($r)->select('div > a');
});
```

Call multiple targets at once:
```php
$urls = [
	'http://foo.bar/user1',
    'http://foo.bar/user2',
    'http://foo.bar/user3',
];
$responseGen = \Raq\fetchAll($urls)->queryAll(function(QueryBuilder $q, ResponseInterface $r) {
	return $q->createXmlQuery($r)->nodes()->each(function(DomElement $e) {
    	if($e->tagName !== 'fooBar') return $e->tagName;
        return null;
    });
});
// no promise is resolved yet.
foreach($responseGen as $queryResult) {
	// only the promise that is currently accessed is resolved.
    // if the loop is broken out prematurely no search is performed on yet
    // unprocessed values. The promises will be free to be cleaned as $responseGen goes out of scope
    var_dump($queryResult);
}
```
