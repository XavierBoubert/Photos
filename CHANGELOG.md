<a name="1.2.0"></a>
# 1.2.0 (2014-?)

## Bug Fixes

- **Common**
  - Display true favicons when no logged user
  - Fix broken folders access with utf8 special characters

- **Admin**
  - Update global count when hidding albums, photos or videos

- **Search**
  - Fix broken tags and identities with spaces

## Breaking Changes

- **First use**
  - Generate cache folder and copy default banner and logo inside

- **Worker**
  - Generate videos thumbnails from frame 100 instead of 1
  - Generate newer folders first to optimize perfs

<a name="1.1.0"></a>
# 1.1.0 (2014-04-17)


## Bug Fixes

- **Security**
  - Fix replace photo by access denied picture for no logged user

- **Explorer**
  - Fix reset photo/video global counts

- **Worker**
  - Fix Worker process PHP time limit

- **Viewer**
  - Hide menu button on videos


## Breaking Changes

- **Explorer**
  - Can reset entire folder to reprocess it

- **Worker**
  - Add more stats informations in Worker progress bar only in _admin_ mode
  - Add stop process button in Worker progress bar
  - Thumbnails generation performances improvement

- **Customize**
  - Move customize files to samples usable by adminsys

<a name="1.0.0"></a>
# 1.0.0 (2014-04-11)

First project stable version.