<?php
// Run with wp eval-file. Read-only smoke checks; no catalog mutations.
defined('ABSPATH') || exit;
wp_set_current_user(0);
if (GMR_Core_Artwork_Images::authorized(6380) || '' !== GMR_Core_Artwork_Images::url(6380)) throw new RuntimeException('Anonymous access');
echo "Anonymous private image denied; no private URL: OK\n";
$raw=(int)get_post_meta(6380,'_thumbnail_id',true);
$public=(int)get_post_meta(6380,'_gmr_public_image',true);
$expected=$public&&wp_attachment_is_image($public)?$public:$raw;
if ((int)get_post_thumbnail_id(6380)!==$expected) throw new RuntimeException('Public image selection changed');
echo $public ? "Public image selected: OK\n" : "Featured image fallback: OK\n";
if (!is_dir(GMR_Core_Documents::vault_path())) throw new RuntimeException('Missing vault');
echo "Private vault exists: OK\n";
