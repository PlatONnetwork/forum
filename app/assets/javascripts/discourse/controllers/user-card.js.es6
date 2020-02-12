import { inject as service } from "@ember/service";
import { inject } from "@ember/controller";
import Controller from "@ember/controller";
import DiscourseURL, { userPath, groupPath } from "discourse/lib/url";

export default Controller.extend({
  topic: inject(),
  router: service(),

  actions: {
    togglePosts(user) {
      const topicController = this.topic;
      topicController.send("toggleParticipant", user);
    },

    showUser(user) {
      DiscourseURL.routeTo(userPath(user.username_lower));
    },

    showGroup(group) {
      DiscourseURL.routeTo(groupPath(group.name));
    }
  }
});
