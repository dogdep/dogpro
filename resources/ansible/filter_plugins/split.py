import re

def split_string(string, seperator=' '):
    return string.split(seperator)

def split_regex(string, seperator_pattern):
    return re.split(seperator_pattern, string)

class FilterModule(object):
    ''' A filter to split a string into a list. '''
    def filters(self):
        return {
            'split' : split_string,
            'split_regex' : split_regex,
        }
