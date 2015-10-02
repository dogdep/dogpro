import os
import time
import json
import sys

class CallbackModule(object):
    """
    This is a very trivial example of how any callback function can get at play and task objects.
    play will be 'None' for runner invocations, and task will be None for 'setup' invocations.
    """

    def on_any(self, *args, **kwargs):
        sys.stdout.flush()


