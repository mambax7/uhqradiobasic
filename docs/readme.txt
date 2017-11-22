uhq-radiobasic :: readme.txt

==[ About ]==

This is the basic version of the uhq-radio module.

This module supports retrieving the current status, what is playing, and how many people are listening to a given station on a single streaming server in a single block at a given time.

This module supports retrieving stats from both Shoutcast and Icecast 2.x servers, and will support a single failover mount on Icecast if it is configured.

More advanced features, such as shadow master servers and multiple streaming servers are developed for and implemented in the main uhq-radio module.  Please be aware that the main module is under consistent development and may have higher requirements than this basic module.

Server admins may want to cache the module at 30-second intervals on a busy site to keep loading and traffic to the streaming server on the low side.

==[ Setup ]==

Please uninstall any version of RadioBasic prior to v1.01.  Otherwise, you may run the risk of a whitescreen.

This module is a single block which extracts information from a single stream server.

--[ General Options ]--

* Server IP/FQDN
  The server IP we'll be getting station info from.

* Server Port
  The port to query on.

* Server Type
  Icecast or Shoutcast.

* Stats PW
  Password needed to access the stats page.  For Shoutcast servers, this is the same password a stream source uses to connect.  For Icecast servers, this is the administartor password.

--[ IceCast Options ]--

The following options only apply if you're pulling information from an Icecast server.

* Icecast Mount
  If using Icecast, get the song information from this mount.

* Mount Type
  Ogg or MPEG.  Ogg mounts have the artist and title information separate.  MP3 and AAC streams are encoded in an MPEG container, and the artist/title are in one field.

* Use Fallback?
  If the block can't find the primary mountpoint, look for information from the secondary one.

* Fallback Mount
  If using Icecast, the backup mount to get song info from.  As the Icecast documentation states, this mount should be configure the same as the primary mount.

* Icecast Stats Username
  This is the administrator username used to get the stats XML.

--[ Show Name Options ]--

Currently, show names can only be drawn from the following, per server type:

Shoutcast.: <STREAMTITLE>
Icecast...: <server_description> within the mount used.

* Use/Display show name?
  Set to Yes to process the show names.

* Start:
  Set to SOL (Start-of-line), or use the first instance of the following delimter.

* End:
  Set to EOL (end-of-line), or use the first instance of the following delimiter.

The end delimiter is processed _after_ the start delimiter, so you can encode multiple pieces of information in the XML field, if you feel so inclined.  I advise making the start and end delimiter different, especially between different pieces of information.

For example, a station which encodes the DJ name in the show between braces would configure their starting delimiter as "{" and the ending delimiter as "}".  In order to effectively use a delimiter, the character sequence used cannot appear in a show name since the processing of the start and end delimiters match on the first sequence.

Whitespace is trimmed as necessary for the show name, so this does not need to be accounted for within the delimiters themselves.

--[ Tune-In Links ]--

This section will determine if a Tune-In link should be displayed on the block, and if so how.

* Display Tune-In Link?
  Select Yes to show the tune-in link.

* Link URL
  This is the web page which instructs your listeners to tune-in.  This can be a PLS or M3U file, but that's not really recommended.

* Target
  This determines where the page opens.  You can select the current window, a new window, or a pop-up window.

* Pop-Up:
  These two fields define the width and height of the pop-up window.  Adjust these numbers to best fit your tune-in window.

There is no dynamically-generated goodness in the tune-in webpage since there may be a huge number of options to choose from and configure.

--[ Other Options ]--

* Display Listeners?
  Select Yes to show how many clients are connected to the stream.

* Show Offline Errors?
  Select Yes to display why the block has determined offline status.  Usually good to set to "no" in a production site.  Errors confuse listeners.

==[ Bug Reports ]==

You may drop me bug reports: xoops@underwood-hq.org.

If there is a specific bug you are experiencing, the more information I have the better.

++I;