jQuery(document).ready(function ($) {
  // Chat icon upload
  $("#oacb_upload_chat_icon").click(function (e) {
    e.preventDefault();

    var mediaUploader = wp.media({
      title: "Select Chat Icon",
      button: {
        text: "Use this icon",
      },
      multiple: false,
      library: {
        type: "image",
      },
    });

    mediaUploader.on("select", function () {
      var attachment = mediaUploader.state().get("selection").first().toJSON();
      $("#oacb_chat_icon_field").val(attachment.url);
    });

    mediaUploader.open();
  });

  // Bot thumbnail upload
  $("#oacb_upload_bot_thumb").click(function (e) {
    e.preventDefault();

    var mediaUploader = wp.media({
      title: "Select Bot Thumbnail",
      button: {
        text: "Use this thumbnail",
      },
      multiple: false,
      library: {
        type: "image",
      },
    });

    mediaUploader.on("select", function () {
      var attachment = mediaUploader.state().get("selection").first().toJSON();
      $("#oacb_bot_thumb_field").val(attachment.url);
    });

    mediaUploader.open();
  });
});
