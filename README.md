> Still in progress

# Intro 

This will be a Joomla CLI script to migrate JComments comments into Kunena forum.

I think, it will be too specific to my personal needs, but some approaches can be used by other developers.

I'm not sure, if I will have time and passion to make it usable without any customization.

## Initial info

Since I use Falang, the script also searches for Content itemn topics in FaLang database.

So if JComments are under an article "Today is my birthday", then the article title would become a Kunena category. 
And each  comment thread would be topic with posts. 

Since I use multilanguage, I also create top Kunena categories for each language.

So `English article` -> `Comment of a user` -> `Admin reply to it` would become a Kunena 
`Top level English category` -> `Article title as a Category` -> `Topic as the firts comment` 
-> `Admin reply as a topic sequent post`.

## JComments for non-articles

I develop the script to migrate JComments under Akeeba Release System into a Kunena-driven support forum. 
So article comments for me are not so important. I hope to make the script universal to work with JComments attached 
to any object.

# Prerequisits

* 3.8+ Joomla with JComments and Kunena fresh forum installed.
* Multilanguage support
