<?php
echo "<b>Built in function unit test...(16/19)</b>";
foreach (array(
		// Should be valid
		'simple@example.com',
		'very.common@example.com',
		'disposable.style.email.with+symbol@example.com',
		'other.email-with-hyphen@example.com',
		'fully-qualified-domain@example.com',
		'user.name+tag+sorting@example.com', 
		'x@example.com',
		'example-indeed@strange-example.com',
		'admin@mailserver1', 
		'example@s.example',
		'" "@example.org',
		',"john..doe"@example.org',
		// Should be invalid
		'Abc.example.com',
		'A@b@c@example.com',
		'a"b(c)d,e:f;g<h>i[j\k]l@example.com',
		'just"not"right@example.com',
		'this is"not\allowed@example.com',
		'this\ still\"not\\allowed@example.com', 
		'1234567890123456789012345678901234567890123456789012345678901234+x@example.com',
    ) as $address) {
    echo "<p>$address is <b>".(filter_var($address, FILTER_VALIDATE_EMAIL) ? '' : 'not')." valid</b></p>";
}

echo "<br><br>";
echo "<b>Regular expression function unit test...(13/19)</b>";
$regex = "/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/";
foreach (array(
		// Should be valid
		'simple@example.com',
		'very.common@example.com',
		'disposable.style.email.with+symbol@example.com',
		'other.email-with-hyphen@example.com',
		'fully-qualified-domain@example.com',
		'user.name+tag+sorting@example.com', 
		'x@example.com',
		'example-indeed@strange-example.com',
		'admin@mailserver1', 
		'example@s.example',
		'" "@example.org',
		',"john..doe"@example.org',
		// Should be invalid
		'Abc.example.com',
		'A@b@c@example.com',
		'a"b(c)d,e:f;g<h>i[j\k]l@example.com',
		'just"not"right@example.com',
		'this is"not\allowed@example.com',
		'this\ still\"not\\allowed@example.com', 
		'1234567890123456789012345678901234567890123456789012345678901234+x@example.com',
    ) as $address) {
    echo "<p>$address is <b>".(preg_match($regex, $address) ? '' : 'not')." valid</b></p>";
}
?>