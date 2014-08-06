## PennGroupsBundle ##

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
        cache_timeout:  600   ## must be an integer value       
        
If you are having trouble with LDAP connections to penngroups, it could be
a certificate issue. You can disable certificate checks (in development 
environments) by updating your /etc/ldap/ldap.conf file with the setting:

    TLS_REQCERT  never

  
This bundle defines three services in the Symfony container:

* penngroups.ldap_query:
* penngroups.web_service_query:
* penngroups.query_cache: 

### LDAP Query ###

Use the PennGroups LDAP query for quick translation of penn_id to pennkey or
pennkey to penn_id.


### Web Service Query ###

Use the PennGroups Web Service query to retrieve name and email data for
a pennkey or penn_id. This is good for one-time lookups like batch processes.


### PennGroups Query Cache ###
 
The PennGroups Query Cache is a wrapper around the Web Service query. Query 
results are stored in the user session. This is useful in a web app where the 
same query is performed several times for the same user in a short timespan.