<h3><?php esc_html_e( 'Default Configuration', 'aip-sf' ); ?></h3>
<dl>
	<dt><h4><?php esc_html_e( 'Petitions', 'aip-sf' ); ?></h4></dt>
	<dd>
		<strong><?php esc_html_e( 'Petitions are stored as "Campaign" Objects. WordPress post fields stored are:', 'aip-sf' ); ?></strong>
		<ul>
			<li><?php esc_html_e( 'Petition Title', 'aip-sf' ); ?> => <?php esc_html_e( 'Campaign Name', 'aip-sf' ); ?></li>
			<li><?php esc_html_e( 'Petition Published Date', 'aip-sf' ); ?> => <?php esc_html_e( 'Campaign Start Date', 'aip-sf' ); ?></li>
		</ul>
		<strong><?php esc_html_e( 'Additional fields stored are:', 'aip-sf' ); ?></strong>
		<ul>
			<li><?php esc_html_e( 'Campaign Type', 'aip-sf' ); ?> => <?php esc_html_e( 'Direct Mail', 'aip-sf' ); ?></li>
			<li><?php esc_html_e( 'Campaign Status', 'aip-sf' ); ?> => <?php esc_html_e( 'In Progress', 'aip-sf' ); ?></li>
		</ul>
	</dd>
	<dt><h4><?php esc_html_e( 'Signatories (Users)', 'aip-sf' ); ?></h4></dt>
	<dd>
		<strong><?php esc_html_e( 'Users who sign petitions are stored as "Contact" Objects. Form fields stored are:', 'aip-sf' ); ?></strong>
		<ul>
			<li><?php esc_html_e( 'First Name', 'aip-sf' ); ?></li>
			<li><?php esc_html_e( 'Last Name', 'aip-sf' ); ?></li>
			<li><?php esc_html_e( 'Email Address', 'aip-sf' ); ?></li>
			<li><?php esc_html_e( 'Telephone (optionally)', 'aip-sf' ); ?></li>
			<li><?php esc_html_e( 'Newsletter Opt-in/out', 'aip-sf' ); ?></li>
		</ul>
	</dd>
	<dt><h4><?php esc_html_e( 'Petition Signatures', 'aip-sf' ); ?></h4></dt>
	<dd>
		<span><?php esc_html_e( 'The relationship between a Petition and a Signatory is a "Campaign Member" Object', 'aip-sf' ); ?></span><br>
		<span><?php esc_html_e( 'A Campaign Member is not generally something that is directly visible within Salesforce, but it allows one to view which Contacts have signed which Campaigns', 'aip-sf' ); ?></span>
	</dd>
</dl>
