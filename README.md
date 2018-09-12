# SmallKeyring

SmallKeyring is a small symfony app to manage access secrets.

You can store your password and other important data in a secured area.

Their is two security modes :
- Without passphrase : your data is encrypted
- With passphrase : a passphrase is used to generate encryption key and this passphrase is never persisted and will be asked at each connection

When using passphrase, even the owner of the site can't crack your data.

### Installation
We recommend using docker environment at [sebk69/SmallKeyring-docker](https://github.com/sebk69/SmallKeyring-docker)

### LICENCE
This app is under GNU GPL V3 licence

@ 2018 - Sébastien Kus