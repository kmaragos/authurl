About
-----

An easy way to retrieve in PHP the contents of a page accessible with cookie authentication, without using a cookie file.

It was built to get pages from private torrent trackers with a specific auth process:
  1. A login form is presented to be filled with username and password
  2. A user ID (uid) and pass code are returned in a cookie
  3. Get the desired page, sending the cookie info in the request header

Well, this is why I built it. It might work in other cases (sites) out of the box or with some tinkering.

TODO
----

* Make this more generic, turn it into a class or sth
* Add search & download function for torrents (throw in a 'thanks' button click)

