# (c) 2012, Daniel Hokka Zakrisson <daniel@hozac.com>
#
# This file is part of Ansible
#
# Ansible is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Ansible is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Ansible.  If not, see <http://www.gnu.org/licenses/>.

import socket
import urllib
import urlparse
import os
import urllib2
import sys
import json
import httplib
import ssl
from ansible import utils, errors

VAULT_URL    = ''
VAULT_APP_ID = ''
VAULT_SECRET = ''

if os.getenv('VAULT_URL') is not None:
    VAULT_URL = os.environ['VAULT_URL']

if os.getenv('VAULT_APP_ID') is not None:
    VAULT_APP_ID = os.environ['VAULT_APP_ID']

if os.getenv('VAULT_SECRET') is not None:
    VAULT_SECRET = os.environ['VAULT_SECRET']

class DataDogVault(object):
    def __init__(self, url=VAULT_URL, validate_certs=False):
        self.url = url
        self.validate_certs = validate_certs

    def get(self, key):

        if "@" in key:
            parts = key.split("@", 1)
            url = "%s/deployKey?%s" % (self.url, urllib.urlencode({
                "user": parts[0],
                "host": parts[1],
            }))
        elif "#" in key:
            parts = key.split("#", 1)
            url = "%s/keyByTag?%s" % (self.url, urllib.urlencode({
                "project": parts[0],
                "tags[]": parts[1].split("|", 5),
            }, True))
        else:
            raise Exception("Invalid format: must be ID#TAG1|TAG2 or user@address");

        authHeader = {"Authorization": "Basic %s:%s" % (VAULT_APP_ID, VAULT_SECRET)}

        try:
            r = open_url(url, validate_certs=self.validate_certs, headers=authHeader)
            key = json.loads(r.read())
            return key
        except urllib2.HTTPError as e:
            raise Exception("%s returned %s" % (e.geturl(), e.getcode()))
        except:
            print url
            raise

class LookupModule(object):

    def __init__(self, basedir=None, **kwargs):
        self.basedir = basedir

    def run(self, terms, inject=None, **kwargs):

        terms = utils.listify_lookup_plugin_terms(terms, self.basedir, inject)

        if isinstance(terms, basestring):
            terms = [ terms ]

        vault = DataDogVault()

        ret = []

        for term in terms:
            key = term.split()[0]
            value = vault.get(key)
            ret.append(value["username"])

        return ret


# Rewrite of fetch_url to not require the module environment
def open_url(url, data=None, headers=None, method=None, use_proxy=True,
        force=False, last_mod_time=None, timeout=10, validate_certs=True,
        url_username=None, url_password=None):
    '''
    Fetches a file from an HTTP/FTP server using urllib2
    '''
    handlers = []
    opener = urllib2.build_opener(*handlers)
    urllib2.install_opener(opener)

    if method:
        if method.upper() not in ('OPTIONS','GET','HEAD','POST','PUT','DELETE','TRACE','CONNECT'):
            raise ConnectionError('invalid HTTP request method; %s' % method.upper())
        request = RequestWithMethod(url, method.upper(), data)
    else:
        request = urllib2.Request(url, data)

    # user defined headers now, which may override things we've set above
    if headers:
        for header in headers:
            request.add_header(header, headers[header])

    if sys.version_info < (2,6,0):
        # urlopen in python prior to 2.6.0 did not
        # have a timeout parameter
        r = urllib2.urlopen(request, None)
    else:
        r = urllib2.urlopen(request, None, timeout)

    return r
