This bundle provides an interface to the PennGroups server for queries
via LDAP or Webservice. 

To use this bundle, add the repo to your composer.json file:

    "repositories" : [{
            "type" : "vcs",
            "url" : "https://github.com/sas-irad/penngroups-bundle"
        }
    ],


Add the bundle to your "require" section:

    "require": {
        "sas-irad/penngroups-bundle": "[your desired version]"
    },

Set the parameters in your parameters.yml files:

    penngroups:
        username:       "kerberos principle"
        credential:     "%kernel.root_dir%/config/keys/penngroups/encrypted.txt"
        key:            "%kernel.root_dir%/config/keys/penngroups/private.pem"
        cache_timeout:  600          
        
If you are having trouble with LDAP connections to penngroups, it could be
a certificate issue. You can disable certificate checks (in development 
environments) by updating your /etc/ldap/ldap.conf file with the setting:

  TLS_REQCERT  never
