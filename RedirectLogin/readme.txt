Redirect on Login and/or Logout mod.
Author: P.J.Lawrence October 2008

Introduction.
This mod will allow admins to be able to select the URLs where users will be directed to after they have either login or logout.

Once installed... 
In version 1 SMF go to the Features and Options, Basic Settings area of your admin panel.
For version 2 SMF go to Miscellaneous under Configeration, Modifications of your admin panel.
Then..
-Find the enable logon redirect and logout settings.
-Select whether you want to redirect the user when they login and/or logout.
-Enter the required URLs.

Notes...
If you don’t provide the full url path (i.e. http://yoursite.xxx/home.html) SMF assumes it’s an SMF function and appends http://yoursite.xxx/index.php? before the URL address. 
For example if you specify action=forum this automatically becomes http://yoursite.xxx/index.php?action=forum
Other examples, to send the user to the forum front page enter the URL as action=forum, to send them to board one enter board=1.0 etc etc...
To send them to another webpage enter http://yoursite.xxx/mywebpage and so on.

If for some reason you are informed that your browser cannot display a webpage when you login/out. 
Simply retype your forum web address and go to feature and option settings and correct the login or logout URL loaction.
Alternatively type http://yoursite.xxx/index.php?action=featuresettings which will take you directly to your forum setting page if the problem occurred when logging in.

This mod only redirects the user if the $_SESSION['login_url'] or $_SESSION['logout_url'] are not set so should be compatible with other mods which set these variables.

Disclaimer: This mod is provided "as is" without express or implied warranty.

Changelog
	0.2 - October, 2008 (First release)
	0.3 - 20th October, 2008 (Support for SMF Version 2.0 Beta 4)
	0.4 - 26th December 2009 Updated for SMF 2.0 RC 2

SMF Versions: 1.1.5, 1.1.6 , 2.0 Beta 4