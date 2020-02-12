import { isEmpty } from "@ember/utils";
import { alias } from "@ember/object/computed";
import { schedule } from "@ember/runloop";
import Component from "@ember/component";
import { escapeExpression } from "discourse/lib/utilities";
import discourseComputed from "discourse-common/utils/decorators";
import Sharing from "discourse/lib/sharing";

export default Component.extend({
  tagName: null,

  type: alias("panel.model.type"),

  topic: alias("panel.model.topic"),

  @discourseComputed
  sources() {
    return Sharing.activeSources(this.siteSettings.share_links);
  },

  @discourseComputed("type", "topic.title")
  shareTitle(type, topicTitle) {
    topicTitle = escapeExpression(topicTitle);
    return I18n.t("share.topic_html", { topicTitle });
  },

  @discourseComputed("panel.model.shareUrl", "topic.shareUrl")
  shareUrl(forcedShareUrl, shareUrl) {
    shareUrl = forcedShareUrl || shareUrl;

    if (isEmpty(shareUrl)) {
      return;
    }

    // Relative urls
    if (shareUrl.indexOf("/") === 0) {
      const location = window.location;
      shareUrl = `${location.protocol}//${location.host}${shareUrl}`;
    }

    return encodeURI(shareUrl);
  },

  didInsertElement() {
    this._super(...arguments);

    const shareUrl = this.shareUrl;
    const $linkInput = $(this.element.querySelector(".topic-share-url"));
    const $linkForTouch = $(
      this.element.querySelector(".topic-share-url-for-touch a")
    );

    schedule("afterRender", () => {
      if (!this.capabilities.touch) {
        $linkForTouch.parent().remove();

        $linkInput
          .val(shareUrl)
          .select()
          .focus();
      } else {
        $linkInput.remove();

        $linkForTouch.attr("href", shareUrl).text(shareUrl);

        const range = window.document.createRange();
        range.selectNode($linkForTouch[0]);
        window.getSelection().addRange(range);
      }
    });
  },

  actions: {
    share(source) {
      Sharing.shareSource(source, {
        url: this.shareUrl,
        title: this.get("topic.title")
      });
    }
  }
});
