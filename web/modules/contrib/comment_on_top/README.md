## INTRODUCTION
The Comment on top module enables users to pin their preferred comment in a node to the top, where it will be displayed
alongside its replies.
Module works on Drupal 10, 9, and 8.

It has been successfully tested on Drupal 10.2.2, 9.5.11, and 8.9.20.

Version 2 is available now!<br>
Now more liked comment can be automatically sticked on top.

Only first tier comment can be sticked on top. Replies/threads cannot be sticked on top, such comments always go with
its parent comment.

## REQUIREMENTS

Standard core dependencies are Comment, Text, Field, Filter, User, System and Node.
If you use submodule Comment on top by likes, you need contribute module Like/Dislike https://www.drupal.org/project/like_dislike

## INSTALLATION

Install as you would normally install a contributed Drupal module.
See: https://www.drupal.org/documentation/install/modules-themes/modules-8 for further information.
If you want the most liked comment to be sticked to the top, there is a submodule called 'Comment on Top by Likes',
which also needs to be enabled.

## CONFIGURATION

COMMENT ON TOP module:

The module is based on the Views block, which means that the default query display comments must be disabled.
So you need to follow the steps below. Or take a look at this video https://www.youtube.com/watch?v=_ffaI9N5B8U

After module installation, you'll get a new View block called "Comments with one sticked on top".
So you need to put it in the right place:
- Go to /admin/structure/block and in region "Content" click on the "Place block" button.
- Select block "Comments with one sticked on top", uncheck "Display title" and save it.
- If block is not placed under "Main page content", use drag'n'drop to move it under.


The next step is to disable the default query comment display, otherwise, you'll have doubled comments:
- Go to EVERY Content type where you have the Comment type field here /admin/structure/types/.
In this case, it will be an Article content type.
- Click on Article "Manage fields" and go to the tab "Manage display".
- Use drag'n'drop to move the "Comments" field under where the "Disabled" fields are, and save it.
- Repeat the last three steps for EVERY content type where you have Comments.


Setting permissions and using module:
- Set permissions for roles that have access to Stick comments on top,
by checking it here /admin/people/permissions/module/comment_on_top.
- Finally, go to the node where you have comments, for example /node/1.
If you are logged in as a user who has the role to Stick comment on top, you will now get the "Stick on top" button
under every comment that is not a reply.
- When you click on "Stick on top" on a favorite comment, page will refresh, and that
comment with its replies will be on top.
- If you want to change your favorite comment for that node, what will be on top,
just click the below comment "Stick on top", and it will replace the current Comment on top.
- If you want default sorting, just click on the top comment "Remove from top".


<br>
COMMENT ON TOP BY LIKES submodule:

This module doesn't have special configuration. Firstly, you need to install and configure Like/Dislike module.
That means adding field_like_dislike in /admin/structure/comment/manage/comment/fields.
That gives Like and Dislike buttons on every comment.

The submodule Comment on top by likes calculates the most liked comment by subtracting dislikes from likes and
sticks it to top.

There are two ways to select nodes where most liked comment will be sticked on top:

- Checkbox "Stick on top by likes" in node create/edit form under Comment settings;
- In /admin/config/system/content-type-comment-on-top-by-likes-settings you can choose content types where all nodes
will have comments sticked on top by likes;

## UNINSTALLATION

COMMENT ON TOP module:

To uninstall Comment on top module and return all to default, please follow the below steps.
Or take a look this video https://youtu.be/_ffaI9N5B8U?t=293

Remove block "Comments with one sticked on top"

- Go to /admin/structure/block/ and under "Content" region, there is a "Comments with one sticked on top" block.
- Click on the down arrow near Configure on that block and click "Remove". Confirm the remove on the pop-up window.

Remove the Stick on top field from Comment type:

- Go to /admin/structure/comment/ and click on "Manage fields".
- On "Stick on top" field, click on down the arrow near Edit and click on Delete.
- Confirm deletion in the pop-up window.

Uninstall Comment on top module:

- Go to /admin/modules/uninstall to uninstall the module.
- From all modules, check "Comment on top" and click the Uninstall button. Confirm it on the next screen.

Returning the default query displaying comments:

- Go to EVERY Content type where you disabled the Comment type field here /admin/structure/types/.
- For example, click on Article "Manage fields" and go to the tab "Manage display".
- Use drag'n'drop to move the "Comments" field above the "Disabled" fields to enable it and save it.
- Repeat last three steps for EVERY content type where you have Comments.

<br>
COMMENT ON TOP BY LIKES submodule:

Uninstall it as every other module, go to /admin/modules/uninstall to uninstall the module.

## MAINTAINERS

Current maintainer for Drupal 10:

- Drazen Musa (drale01) - https://www.drupal.org/u/drale01
