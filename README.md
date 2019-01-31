# Impartmedia Bookeasy Wordpress Plugin
A plugin that will import bookeasy operators information in to a post type in wordpress via: 

https://webapi.impartmedia.com/api/getOperatorsInformation?q=vc_id


# Install

1. Download the repo and unzip it into your plugins folder.
2. Rename it to bookeasy if you like.
3. Enable the plugin via the admin section of the site
4. Go to Bookeasy menu /wp-admin/admin.php?page=bookeasy > Config 
5. Set the VC ID, post types, and api keys 
6. Run the sync from the sync tab

# Cron

```* * * * * path/to/cron.sh ```
