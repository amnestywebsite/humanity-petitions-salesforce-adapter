# Amnesty Petitions Salesforce Adapter
This is a companion plugin for the primary [Humanity Petitions](https://github.com/amnestywebsite/humanity-petitions) plugin, which adds support for submitting donation data directly to Salesforce via their HTTP REST API, instead of storing it within the WP database.  

## Minimum Requirements
This plugin requires:  
- WordPress 5.8+  
- PHP 8.2+ with the Intl extension  
- [Humanity Theme](https://github.com/amnestywebsite/humanity-theme) v1.0.0+  
- [CMB2](https://github.com/CMB2/CMB2)
- [CMB2 Message Field](https://github.com/amnestywebsite/cmb2-message-field)
- [CMB2 Password Field](https://github.com/amnestywebsite/cmb2-password-field)
- [Humanity Petitions](https://github.com/amnestywebsite/humanity-petitions) v1.0.0+
- [Salesforce Connector](https://github.com/amnestywebsite/humanity-salesforce-connector) v1.0.0+  

## Installation
The quickest way to get started using the plugin is to download the zip of the [latest release](https://github.com/amnestywebsite/humanity-petitions-salesforce-adapter/releases/latest), and install it via upload directly within WP Admin -> Plugins.  

## Configuration
Once activated, this plugin adds a settings page below the primary Salesforce Settings page in Network Admin. If you haven’t configured the Salesforce Connector plugin, do that first.  

Once setup has been completed, you’ll start seeing data within Salesforce as soon as your users start submitting petition signatures. By default, the plugin creates a new Campaign in Salesforce for each petition. Each signatory is created or updated as a Contact, and the Contacts are added as Campaign Members on the appropriate petition.  

To configure alternative objects, choose the “Customise” option in the settings.  

Four sections will become visible, which control each of the different aspects of the data that can be recorded. Each section has some contextual information which should clarify the different options available. Select which Salesforce Object types each of the data types should be saved as, and the appropriate fields. The plugin will handle the rest.  

## Governance
See [GOVERNANCE.md](GOVERNANCE.md) for project governance information.  

## Changelog  
See [CHANGELOG.md](CHANGELOG.md) or [Releases page](https://github.com/amnestywebsite/humanity-petitions-salesforce-adapter/releases) for full changelogs.

## Contributing
For information on how to contribute to the project, or to get set up locally for development, please see the documentation in [CONTRIBUTING.md](CONTRIBUTING.md).  

### Special Thanks
We'd like to say a special thank you to these lovely folks:

| &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[Cure53](https://cure53.de)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[WP Engine](https://wpengine.com)&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; |
| --- | --- |
| ![Cure53](./docs/static/cure_53_logo.svg) | ![WP Engine](./docs/static/wpengine_logo.svg) |


### Want to know more about the work in other Amnesty GitHub accounts?

You can find repositories from other teams such as [Amnesty Web Ops](https://github.com/amnestywebsite), [Amnesty Crisis](https://github.com/amnesty-crisis-evidence-lab), [Amnesty Tech](https://github.com/AmnestyTech), and [Amnesty Research](https://github.com/amnestyresearch/) in their GitHub accounts

![AmnestyWebsiteFooter](https://wordpresstheme.amnesty.org/wp-content/uploads/2024/02/footer.gif)
