<div>
	<h3><?php esc_html_e( 'Configure the means for retrieving the count of signatories on a petition', 'aip-sf' ); ?></h3>
	<p><strong><?php esc_html_e( 'Options', 'aip-sf' ); ?></strong></p>
	<ul>
		<li>
			<em><?php esc_html_e( 'Field on Object', 'aip-sf' ); ?></em><br>
			<span><?php esc_html_e( 'Retrieve the count from an existing field on a Salesforce Object', 'aip-sf' ); ?></span>
		</li>
		<li>
			<em><?php esc_html_e( 'SOQL Query', 'aip-sf' ); ?></em><br>
			<span><?php esc_html_e( 'Perform a query on the Salesforce API. This is useful when the petitions are not stored as Objects in Salesforce', 'aip-sf' ); ?></span>
		</li>
	</ul>

	<hr>

	<p><strong><?php esc_html_e( 'Field on Object', 'aip-sf' ); ?></strong></p>
	<p><?php esc_html_e( 'By default, petitions are stored as "Campaign" objects in Salesforce. When signatories are added to the Campaign, the "Contacts in Campaign" property on the object stores how many signatories have signed the petition.', 'aip-sf' ); ?></p>

	<p><strong><?php esc_html_e( 'SOQL Query', 'aip-sf' ); ?></strong></p>
	<p>
	<?php

	echo wp_kses(
		sprintf(
			// translators: %s: code block containing a dynamic variable definition
			__(
				'When configured in certain ways, there is no direct means of retrieving the signatory count from Salesforce. Under such circumstances, a SOQL query can be used to generate a count. For example: if petitions are stored as a "Topic", and signatories are assigned a Topic for each petition they sign, we can query how many signatories are assigned to a Topic using SOQL. The dynamic variable %s can be used to define the insertion point for the petition\'s Salesforce ID',
				'aip-sf'
			),
			'<code>{petition}</code>',
		),
		[ 'code' => true ]
	);

	?>
	</p>
</div>
