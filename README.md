Unit of Validate
===

# Usage

```
<?php
/* @var $validate \OP\UNIT\Validate */
$validate = $app->Unit('validate');

//	...
$result = $validate->Evaluation('required', $value);

```

# Rule

## Number

 * Integer
 * Float
 * Positive
 * Negative
 * min
 * max

## String

 * short
 * long
 * alphabet
 * number
 * alphanumeric
 * english
 * email
 * domain
 * phone
