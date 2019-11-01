# Impartmedia Bookeasy Wordpress Plugin
A plugin that will import bookeasy operators information in to a post type in wordpress via: 

https://webapi.bookeasy.com.au/api/getOperatorsInformation?q=vc_id


# Install

1. Download the repo and unzip it into your plugins folder.
2. Rename it to bookeasy if you like.
3. Enable the plugin via the admin section of the site
4. Go to Bookeasy menu /wp-admin/admin.php?page=bookeasy > Config 
5. Set the VC ID, post types, and api keys 
6. Setup cron.. see below
6. Run the sync from the sync tab

# Cron

### Background cron, actived from sync button

```* * * * * path/to/cron.sh ```

### Forced cron

``` 0 0 * * * /path/to/wp/plugins/bookeasy/api/sync.php ``` 

# Hidden cart for non gadget pages 

```
<a href="/book/" class="cart-button">
    <span class="cart-count" style="display:none;">0</span>
    <i class="fas fa-shopping-cart"></i>
</a>
```
Then add this somewhere at the bottom of the site so it gets 
[bookeasy_hidden_cart]