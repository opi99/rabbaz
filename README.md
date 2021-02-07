# rabbaz
Platform for detectable devices in PHP, hihgly experimental

The main goal
=============

Having a Raspberry Pi Zero W with HifiBerry and a touch display for my dad to have an easy radio station switch and to
play CDs as it is done by a damn easy Multi Disc CD-Player. Originaly, I planed to put all parts together, put software
on it and done. But there were some little zombies, starting from the point that there are less packages vor the ARMv6
cpu and going to user interfaces whcih are done four young people but not for older ones. They know theyr CD collection,
they take them and play all together. They don't start a search, select this and that song. And so I started experimenting
with the raspi and my PHP knowladges. And here we have "Rabbaz"

What is it
==========

I don't know yet, where this will lead, what this will be. Will this be a core framework which the gets divers "products".
Maybe all things centralized here together and extensible with extensions like my best friend TYPO3.

What can it do now
==================

The source on the flash card runs on a Raspberry Pi Zero W with Raspbian bullseye installed and on a second system, which
is a older ThinkPad Laptop with Ubuntu 21.04 both using PHP 7.4.
It includes ScanServices for Local (mpd, alsa), Bluetooth (through Linux blueZ), Ssdp/UPnP, TR-064, WebService/WsDiscovery
and ZeroConf (with Avahi).
All the devices which can be found by scanning, will be listed, but no interaction is yet possible. There are Icons shown,
from the vendors system (UPnP) or from the Assets directory. The icons will be placed (and later loaded) by the
specification of freedesktop.org https://specifications.freedesktop.org/icon-theme-spec/icon-theme-spec-latest.html
The one also used by Gnome/KDE/... Desktop Icon Themes.

What else
=========

Till yet, no button or sound played. But different devices discovered, a HP Printer by WebServices, FRITZ!Box by UPnP
and TR-064, a MacBook by ZeroConf(Bonjour) or headphone by bluetooth.
