<p>
<?php

$kses_options = [
	'code' => true,
	'a'    => true,
];

echo wp_kses(
	sprintf(
		// translators: %1$s: line break, %2$s: documentation link
		__( 'You need to create a Connected App to generate your Client ID/Secret keys; see %2$s for further details.%1$s', 'aip-sf' ),
		'<br>',
		'<a href="https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/intro_oauth_and_connected_apps.htm" target="_blank" rel="noreferrer noopener">Salesforce</a>'
	),
	$kses_options
);

?>
</p>

<p>
<?php
echo wp_kses(
	sprintf(
		// translators: %1$s: line break, %2$s: required permissions, %3$s: callback URL
		__( 'Please ensure that your Connected App has the following oAuth permissions: %2$s.%1$sThe Callback URL must be set to this: %3$s.', 'aip-sf' ),
		'<br>',
		'<code>api</code>, <code>refresh_token, offline_access</code>',
		sprintf( '<code>%s</code>', home_url( '/aip/v1/salesforce/oauth/code', 'https' ) )
	),
	$kses_options
);

?>
</p>

<p>
<?php
echo wp_kses(
	sprintf(
		// translators: %1$s: line break, %2$s: the setting name, %3$s: the Salesforce object name, %4$s: Salesforce helpdesk link
		__( 'When using the default configuration, please ensure that you have permission to modify the %2$s setting on the %3$s object via the API; see %4$s for more information.%1$sIf customising the configuration, please ensure that you are able to modify all fields you select.', 'aip-sf' ),
		'<br>',
		sprintf( '<code>%s</code>', 'HasOptedOutOfEmail' ),
		sprintf( '<code>%s</code>', 'Contact' ),
		'<a href="https://help.salesforce.com/articleView?id=000336669&language=en_US&type=1&mode=1" target="_blank" rel="noopener noreferrer">Salesforce Helpdesk</a>',
	),
	$kses_options
);

?>
</p>
