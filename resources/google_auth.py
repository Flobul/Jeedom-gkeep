#!/usr/bin/env python3
"""Exchange the browser cookie over stdin; never emit credentials on failure."""
import json
import re
import signal
import sys


def exchange(payload):
    import gpsoauth
    import gkeepapi
    email = str(payload.get('email', '')).strip()
    cookie = str(payload.get('oauth_token', '')).strip()
    android = str(payload.get('android_id', '')).strip()
    if not email or not cookie or not re.fullmatch(r'[0-9a-fA-F]{16}', android):
        raise ValueError('invalid input')
    response = gpsoauth.exchange_token(email, cookie, android)
    token = response.get('Token')
    if not token:
        raise ValueError('exchange refused')
    keep = gkeepapi.Keep()
    if hasattr(keep, 'authenticate'):
        keep.authenticate(email, token)
    else:
        if not keep.resume(email, token):
            raise ValueError('Keep refused token')
    return token


def main():
    signal.signal(signal.SIGALRM, lambda *_: (_ for _ in ()).throw(TimeoutError()))
    signal.alarm(90)
    try:
        payload = json.loads(sys.stdin.read(32768))
        token = exchange(payload)
        print(json.dumps({'token':token}))
    except Exception:
        print(json.dumps({'error':'Authentification Google refusée ou indisponible. Vérifiez les dépendances, le compte et un cookie oauth_token récent.'}))
        return 1
    finally:
        signal.alarm(0)
    return 0


if __name__ == '__main__':
    sys.exit(main())
