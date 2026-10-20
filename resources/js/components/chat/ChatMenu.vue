<template>
  <VoiceCallDialog
    v-model="voiceCallOpen"
    :user="repo.activeUser"
    :status="voiceStatus"
    :muted="muted"
    @accept="acceptVoice"
    @end="endVoice"
    ref="voiceDialogRef"
    @toggle-mute="toggleMute"
  />

  <VideoCallDialog
    v-model="videoCallOpen"
    :user="repo.activeUser"
    :status="videoStatus"
    :muted="muted"
    :cameraOff="cameraOff"
    @accept="acceptVideo"
    @end="endVideo"
    @toggle-mute="toggleMute"
    @toggle-camera="toggleCamera"
    ref="videoDialogRef"
  />

  <v-menu
    v-model="menuOpen"
    location="bottom end"
    :close-on-content-click="false"
    transition="slide-y-transition"
    content-class="chat-menu"
    :max-width="950"
    min-width="850"
    rounded="xl"
  >
    <!-- activator -->
    <template #activator="{ props }">
      <v-badge
        :content="unreadCount"
        :model-value="unreadCount > 0"
        color="error"
        overlap
        location="top end"
        offset-x="6"
        offset-y="6"
        bordered
        class="chat-badge"
      >
        <v-btn
          v-bind="props"
          icon
          size="default"
          variant="tonal"
          class="chat-activator"
          width="44"
          height="44"
          :loading="repo.bootLoading"
          @click="onActivatorClick"
        >
          <v-icon size="22" color="primary">mdi-whatsapp</v-icon>
        </v-btn>
      </v-badge>
    </template>

    <!-- panel -->
    <v-card class="chat-card" rounded="xl">
      <!-- HEADER -->
      <div class="chat-header" :dir="chatDir">
        <div class="d-flex align-center gap-3">
          <div class="header-icon">
            <v-icon size="26" color="white">mdi-forum</v-icon>
          </div>
          <div class="header-content">
            <div class="title">{{ tr('chat.title') }}</div>
            <div class="subtitle">{{ tr('chat.subtitle') }}</div>
          </div>
        </div>

        <div class="header-actions">
          <!-- ✅ LOCAL language (only inside this menu) -->
          <v-menu location="bottom end" :close-on-content-click="true">
            <template #activator="{ props: langProps }">
              <v-btn icon variant="text" size="small" class="action-btn" v-bind="langProps">
                <v-icon color="white">mdi-translate</v-icon>
                <v-tooltip activator="parent" location="bottom">
                  {{ tr('chat.language') }}
                </v-tooltip>
              </v-btn>
            </template>

            <v-list density="compact" class="language-list">
              <v-list-item :active="chatLocale === 'fa'" @click="setChatLocale('fa')" title="دری" />
              <v-list-item
                :active="chatLocale === 'ps'"
                @click="setChatLocale('ps')"
                title="پښتو"
              />
              <v-list-item
                :active="chatLocale === 'en'"
                @click="setChatLocale('en')"
                title="English"
              />
            </v-list>
          </v-menu>

          <v-btn
            icon
            variant="text"
            size="small"
            class="action-btn"
            :disabled="!repo.activeConversationId"
            @click="toggleSearch"
          >
            <v-icon color="white">mdi-magnify</v-icon>
            <v-tooltip activator="parent" location="bottom">{{ tr('chat.search') }}</v-tooltip>
          </v-btn>

          <v-btn icon variant="text" size="small" class="action-btn" @click="deleteAllChats">
            <v-icon color="white">mdi-delete-sweep</v-icon>
            <v-tooltip activator="parent" location="bottom">
              {{ tr('chat.deleteAllChats') }}
            </v-tooltip>
          </v-btn>

          <v-btn
            icon
            variant="text"
            size="small"
            class="action-btn"
            @click="markAllAsRead"
            :disabled="unreadCount === 0"
          >
            <v-icon color="white">mdi-check-all</v-icon>
            <v-tooltip activator="parent" location="bottom">
              {{ tr('chat.markAllRead') }}
            </v-tooltip>
          </v-btn>

          <v-btn
            icon
            variant="text"
            size="small"
            class="action-btn"
            :loading="repo.silentLoading"
            @click="repo.refreshLists()"
          >
            <v-icon color="white">mdi-refresh</v-icon>
            <v-tooltip activator="parent" location="bottom">{{ tr('chat.refresh') }}</v-tooltip>
          </v-btn>

          <v-btn icon variant="text" size="small" class="action-btn" @click="menuOpen = false">
            <v-icon color="white">mdi-close</v-icon>
            <v-tooltip activator="parent" location="bottom">{{ tr('chat.close') }}</v-tooltip>
          </v-btn>
        </div>
      </div>

      <!-- BODY -->
      <div class="chat-body" :dir="chatDir">
        <!-- LEFT USERS -->
        <div class="chat-left">
          <div class="search-container">
            <v-text-field
              v-model="searchQuery"
              density="compact"
              variant="outlined"
              hide-details
              :placeholder="tr('chat.searchContacts')"
              prepend-inner-icon="mdi-magnify"
              class="users-search"
              clearable
              @click:clear="searchQuery = ''"
              rounded
              single-line
              :dir="chatDir"
            />
          </div>

          <div class="users-scroll thin-scroll">
            <v-list density="compact" class="py-0">
              <v-list-item
                v-for="user in filteredUsers"
                :key="user.id"
                rounded="lg"
                class="user-item"
                :active="repo.activeUser?.id === user.id"
                :active-class="'user-item-active'"
                @click="selectUser(user)"
              >
                <template #prepend>
                  <div class="avatar-wrap">
                    <v-avatar size="48" class="avatar">
                      <v-img v-if="user.image_url" :src="user.image_url" :alt="user.name" />
                      <v-icon v-else size="48" :color="user?.isOnline ? 'primary' : 'grey'">
                        mdi-account-circle
                      </v-icon>
                      <div class="status-dot-avatar" :class="{ online: !!user.isOnline }"></div>
                    </v-avatar>
                  </div>
                </template>

                <div class="user-info">
                  <div class="user-top">
                    <div class="name-section">
                      <v-list-item-title class="name">{{ user.name }}</v-list-item-title>
                    </div>
                    <div class="time" v-if="getLastMessageTime(user)">
                      {{ formatRelativeTime(getLastMessageTime(user)) }}
                    </div>
                  </div>

                  <div class="message-preview">
                    <v-list-item-subtitle class="message-text">
                      {{ getLastMessagePreview(user) }}
                    </v-list-item-subtitle>

                    <div v-if="conversationUnreadByUser(user.id) > 0" class="unread-indicator">
                      {{ conversationUnreadByUser(user.id) }}
                    </div>
                  </div>
                </div>
              </v-list-item>
            </v-list>
          </div>
        </div>

        <!-- RIGHT CHAT -->
        <div class="chat-right" v-if="repo.hasActiveConversation">
          <div class="chat-right-header" :dir="chatDir">
            <div class="active-user-info">
              <v-avatar size="46" class="active-user-avatar">
                <v-img
                  v-if="repo.activeUser.image_url"
                  :src="repo.activeUser.image_url"
                  :alt="repo.activeUser.name"
                  cover
                />
                <v-icon v-else size="46">mdi-account-circle</v-icon>
                <div
                  class="active-status-dot"
                  :class="{ online: !!repo.activeUser.isOnline }"
                ></div>
              </v-avatar>

              <div class="active-user-details">
                <div class="active-user-name">{{ repo.activeUser.name }}</div>
              </div>
            </div>

            <!-- show search next to username when searchOpen -->
            <ChatSearchInline
              v-if="searchOpen"
              ref="searchInlineRef"
              :dir="chatDir"
              :onSearch="doSearch"
              @jump="scrollToMessage"
            />
          </div>
          <!-- Call buttons -->
          <div class="d-flex align-center gap-1">
            <v-btn
              icon
              variant="text"
              size="small"
              class="action-btn"
              :disabled="!repo.activeUser"
              @click="startVoiceCall"
            >
              <v-icon color="primary">mdi-phone</v-icon>
              <v-tooltip activator="parent" location="bottom">Voice call</v-tooltip>
            </v-btn>

            <v-btn
              icon
              variant="text"
              size="small"
              class="action-btn"
              :disabled="!repo.activeUser"
              @click="startVideoCall"
            >
              <v-icon color="primary">mdi-video</v-icon>
              <v-tooltip activator="parent" location="bottom">Video call</v-tooltip>
            </v-btn>
          </div>

          <div
            ref="messagesBox"
            class="messages-box thin-scroll"
            @scroll="handleScroll"
            :dir="chatDir"
          >
            <div
              v-for="(messageGroup, groupIndex) in groupedMessages"
              :key="groupIndex"
              class="message-group"
            >
              <div
                v-for="message in messageGroup"
                :key="message.id"
                class="message-wrapper"
                :data-message-id="message.id"
                :class="{
                  sent: message.sender?.id === repo.currentUser?.id,
                  received: message.sender?.id !== repo.currentUser?.id,
                }"
              >
                <div class="message-bubble-wrapper">
                  <div class="message-bubble">
                    <div v-if="message.deleted_at" class="deleted-text">
                      {{ tr('chat.messageDeleted') }}
                    </div>

                    <div
                      v-else-if="message.replyTo"
                      class="reply-quote"
                      @click="scrollToMessage(message.replyTo.id)"
                    >
                      <div class="reply-quote-top">
                        <v-icon size="14">mdi-reply</v-icon>
                        <span class="reply-quote-name">{{
                          message.replyTo.sender?.name || tr('chat.user')
                        }}</span>
                      </div>
                      <div class="reply-quote-body">
                        {{ truncateText(message.replyTo.body || '', 80) }}
                      </div>
                    </div>

                    <div v-if="message.type === 'call'" class="call-log">
                      <v-icon size="18" class="mr-2">
                        {{ message.call_type === 'video' ? 'mdi-video' : 'mdi-phone' }}
                      </v-icon>

                      <div class="call-log-text">
                        <div class="call-log-title">
                          {{ message.call_type === 'video' ? 'Video call' : 'Voice call' }}
                        </div>

                        <div class="call-log-sub">
                          <span v-if="message.call_status === 'started'">Started</span>
                          <span v-else-if="message.call_status === 'ended'">Ended</span>
                          <span v-else-if="message.call_status === 'missed'">Missed</span>
                          <span v-else-if="message.call_status === 'rejected'">Rejected</span>
                          <span v-else>{{ message.call_status }}</span>

                          <span v-if="message.call_duration"> • {{ message.call_duration }}</span>
                        </div>
                      </div>
                    </div>

                    <div v-if="!message.deleted_at && message.body" class="message-text">
                      {{ message.body }}
                    </div>

                    <div v-if="message.attachments?.length" class="attach-list">
                      <div v-for="a in message.attachments" :key="a.id" class="attach-item">
                        <template v-if="a.type === 'image'">
                          <a :href="a.url" :download="a.name" target="_blank" rel="noopener">
                            <v-img class="attach-img" :src="a.url" :alt="a.name" />
                          </a>
                        </template>

                        <template v-else-if="a.type === 'video'">
                          <a :href="a.url" :download="a.name" target="_blank" rel="noopener">
                            <video class="attach-video" :src="a.url" controls />
                          </a>
                        </template>

                        <template v-else-if="a.type === 'voice'">
                          <a :href="a.url" :download="a.name" target="_blank" rel="noopener">
                            <audio class="attach-audio" :src="a.url" controls />
                          </a>
                        </template>

                        <template v-else>
                          <a
                            class="attach-doc"
                            :href="a.url"
                            :download="a.name"
                            target="_blank"
                            rel="noopener"
                          >
                            <v-icon size="18" color="primary">mdi-file</v-icon>
                            <span>{{ a.name }}</span>
                          </a>
                        </template>
                      </div>
                    </div>

                    <div class="message-meta">
                      <span class="message-time">{{ formatTime(message.created_at) }}</span>

                      <div class="message-status">
                        <v-icon
                          v-if="message.sender?.id === repo.currentUser?.id"
                          size="16"
                          :color="message.seen_at ? 'primary' : 'grey'"
                        >
                          mdi-check-all
                        </v-icon>

                        <v-btn
                          icon
                          size="x-small"
                          variant="text"
                          class="reply-btn"
                          @click="startReply(message)"
                        >
                          <v-icon size="14" color="grey">mdi-reply</v-icon>
                        </v-btn>

                        <v-btn
                          v-if="message.sender?.id === repo.currentUser?.id && !message.deleted_at"
                          icon
                          size="x-small"
                          variant="text"
                          class="reply-btn"
                          @click="deleteOne(message)"
                        >
                          <v-icon size="14" color="error">mdi-delete</v-icon>
                        </v-btn>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div ref="bottomAnchor"></div>
          </div>

          <!-- reply preview -->
          <div v-if="replyToMessage" class="reply-preview" :dir="chatDir">
            <div class="reply-preview-content">
              <div class="reply-header">
                <v-icon size="16" color="primary">mdi-reply</v-icon>
                <span class="reply-title">
                  {{
                    tr('chat.replyingTo', { name: replyToMessage.sender?.name || tr('chat.user') })
                  }}
                </span>
                <v-btn icon size="x-small" variant="text" @click="cancelReply">
                  <v-icon size="16">mdi-close</v-icon>
                </v-btn>
              </div>
              <div class="reply-text">{{ truncateText(replyToMessage.body || '', 50) }}</div>
            </div>
          </div>

          <!-- composer -->
          <div class="composer" :dir="chatDir">
            <v-btn icon variant="text" @click="attachOpen = true">
              <v-icon color="primary">mdi-paperclip</v-icon>
              <v-tooltip activator="parent" location="top">{{ tr('chat.attach') }}</v-tooltip>
            </v-btn>

            <VoiceRecorder
              ref="voiceRef"
              @ready="onVoiceReady"
              @recording-changed="voiceRecording = $event"
            />
            <!-- preview bar -->
            <div v-if="hasPendingAttachments" class="composer-previews">
              <div v-if="attachments.images.length" class="preview-row">
                <div class="preview-label">
                  <v-icon size="16" color="primary">mdi-image</v-icon
                  ><span>{{ tr('chat.images') }}</span>
                </div>

                <div class="preview-items">
                  <div
                    v-for="(f, idx) in attachments.images"
                    :key="`img-${idx}`"
                    class="thumb-wrap"
                  >
                    <v-img class="thumb" :src="makeObjectUrl(f)" :alt="f.name" />
                    <v-btn
                      icon
                      size="x-small"
                      variant="text"
                      class="thumb-x"
                      @click="removeAttach('images', idx)"
                    >
                      <v-icon size="16" color="error">mdi-close</v-icon>
                    </v-btn>
                  </div>
                </div>
              </div>

              <div v-if="attachments.videos.length" class="preview-row">
                <div class="preview-label">
                  <v-icon size="16" color="primary">mdi-video</v-icon
                  ><span>{{ tr('chat.videos') }}</span>
                </div>
                <div class="preview-chips">
                  <v-chip
                    v-for="(f, idx) in attachments.videos"
                    :key="`vid-${idx}`"
                    size="small"
                    closable
                    class="ma-1"
                    @click:close="removeAttach('videos', idx)"
                  >
                    {{ f.name }}
                  </v-chip>
                </div>
              </div>

              <div v-if="attachments.docs.length" class="preview-row">
                <div class="preview-label">
                  <v-icon size="16" color="primary">mdi-file-document</v-icon
                  ><span>{{ tr('chat.docs') }}</span>
                </div>
                <div class="preview-chips">
                  <v-chip
                    v-for="(f, idx) in attachments.docs"
                    :key="`doc-${idx}`"
                    size="small"
                    closable
                    class="ma-1"
                    @click:close="removeAttach('docs', idx)"
                  >
                    {{ f.name }}
                  </v-chip>
                </div>
              </div>

              <div v-if="attachments.voices.length" class="preview-row">
                <div class="preview-label">
                  <v-icon size="16" color="primary">mdi-microphone</v-icon
                  ><span>{{ tr('chat.voices') }}</span>
                </div>
                <div class="preview-chips">
                  <v-chip
                    v-for="(f, idx) in attachments.voices"
                    :key="`voc-${idx}`"
                    size="small"
                    closable
                    class="ma-1"
                    @click:close="removeAttach('voices', idx)"
                  >
                    {{ f.name }}
                  </v-chip>
                </div>
              </div>
            </div>

            <v-text-field
              ref="messageInput"
              v-model="draft"
              density="comfortable"
              variant="outlined"
              hide-details
              :placeholder="replyToMessage ? tr('chat.typeReply') : tr('chat.typeMessage')"
              class="composer-input"
              @keydown.enter.stop.prevent="send"
              rounded
              single-line
            />

            <v-btn
              icon
              color="primary"
              class="send-btn"
              :disabled="!canSend"
              @click="send"
              size="large"
            >
              <v-icon>mdi-send</v-icon>
              <v-tooltip activator="parent" location="top">{{ tr('chat.send') }}</v-tooltip>
            </v-btn>
          </div>
        </div>

        <!-- empty -->
        <div v-else class="chat-empty" :dir="chatDir">
          <div class="empty-content">
            <div class="empty-icon">
              <v-icon size="90" color="primary-lighten-2">mdi-chat-outline</v-icon>
            </div>
            <div class="empty-title">{{ tr('chat.welcome') }}</div>
            <div class="empty-subtitle">{{ tr('chat.selectContact') }}</div>
          </div>
        </div>
      </div>
    </v-card>

    <!-- dialogs -->
    <MediaChat v-model="attachOpen" :value="attachments" @update:value="setAttachments" />
  </v-menu>
</template>

<script setup>
  import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
  import dayjs from 'dayjs';
  import relativeTime from 'dayjs/plugin/relativeTime';
  import localizedFormat from 'dayjs/plugin/localizedFormat';
  import isToday from 'dayjs/plugin/isToday';
  import isYesterday from 'dayjs/plugin/isYesterday';
  import 'dayjs/locale/en';
  import 'dayjs/locale/fa';

  import { useChatRepository } from '@/stores/ChatRepository';
  import MediaChat from '@/components/chat/MediaChat.vue';
  import VoiceRecorder from '@/components/chat/VoiceRecorder.vue';
  import ChatSearchInline from '@/components/chat/ChatSearchInline.vue';
  import VoiceCallDialog from '@/components/chat/VoiceCallDialog.vue';
  import VideoCallDialog from '@/components/chat/VideoCallDialog.vue';

  import { useWebRtcCall } from '@/composables/useWebRtcCall';
  import { v4 as uuidv4 } from 'uuid';
  const videoDialogRef = ref(null);
  const voiceDialogRef = ref(null);
  const receiverAccepted = ref(false);
  const receiverAcceptedAt = ref(null);
  const activeCallId = ref(null);
  const pendingOfferSdp = ref(null);
  const peerSide = ref(null); // caller|receiver
  const callPeerUserId = ref(null);
  const callConversationId = ref(null);
  dayjs.extend(relativeTime);
  dayjs.extend(localizedFormat);
  dayjs.extend(isToday);
  dayjs.extend(isYesterday);

  const repo = useChatRepository();

  // for the voice and video call
  // ========================
  // CALL STATE
  // ========================
  const voiceCallOpen = ref(false);
  const videoCallOpen = ref(false);

  const callType = ref(null); // 'voice'|'video'
  const callStatus = ref('idle'); // idle|ringing|connected|ended|missed|rejected
  const callStartedAt = ref(null);

  const muted = ref(false);
  const cameraOff = ref(false);

  const pendingSignalsByCallId = new Map();
  let currentChannelKey = null;
  const queueSignal = (call) => {
    const id = call?.call_id;
    if (!id) return;
    const arr = pendingSignalsByCallId.get(id) || [];
    arr.push(call);
    pendingSignalsByCallId.set(id, arr);
  };

  const flushSignals = async (callId) => {
    const arr = pendingSignalsByCallId.get(callId) || [];
    pendingSignalsByCallId.delete(callId);
    for (const c of arr) await handleCallSignalNow(c);
  };

  const handleCallSignalNow = async (call) => {
    const sig = call.signal;

    if (sig?.type === 'offer') {
      pendingOfferSdp.value = sig.sdp;
      return;
    }

    if (sig?.type === 'answer') {
      stopRingtone();
      await applyAnswer(sig.sdp);
      return;
    }

    if (sig?.type === 'ice') {
      await addIce(sig.candidate);
    }
  };
  // ringtone (caller/receiver)
  const ringAudio = new Audio('/sounds/ringtone.mp3');
  ringAudio.loop = true;

  const startRingtone = async () => {
    try {
      await ringAudio.play();
    } catch {
      // autoplay blocked until user gesture
    }
  };

  const stopRingtone = () => {
    try {
      ringAudio.pause();
      ringAudio.currentTime = 0;
    } catch {}
  };

  const fmtDuration = (sec) => {
    const s = Math.max(0, Number(sec || 0));
    const mm = String(Math.floor(s / 60)).padStart(2, '0');
    const ss = String(Math.floor(s % 60)).padStart(2, '0');
    return `${mm}:${ss}`;
  };

  const secondsSinceStart = () => {
    if (!callStartedAt.value) return 0;
    return Math.max(0, dayjs().diff(dayjs(callStartedAt.value), 'second'));
  };

  const addCallLogMessage = (payload) => {
    // payload example:
    // { type:'call', call_type:'video', status:'ended', duration:'02:10', atIso:... }
    const tmpId = `call-${Date.now()}-${Math.random().toString(16).slice(2)}`;
    repo.messages.push({
      id: tmpId,
      conversation_id: repo.activeConversationId,
      type: 'call',
      call_type: payload.call_type,
      call_status: payload.status,
      call_duration: payload.duration || null,
      created_at: payload.atIso || new Date().toISOString(),
      sender: repo.currentUser ? { ...repo.currentUser } : { id: repo.currentUser?.id },
    });
  };

  // the rington part

  const startVoiceCall = async () => {
    if (!repo.activeUser) return;

    peerSide.value = 'caller';
    callType.value = 'voice';
    activeCallId.value = uuidv4();
    callPeerUserId.value = repo.activeUser.id;
    callConversationId.value = repo.activeConversationId;
    callStatus.value = 'ringing';
    voiceStatus.value = 'ringing';
    voiceCallOpen.value = true;

    await startRingtone();

    // ✅ permission + mic
    await getMedia({ video: false });
    attachLocalTracks();

    await repo
      .startCall({
        type: 'voice',
        toUserId: repo.activeUser.id,
        conversationId: repo.activeConversationId,
        call_id: activeCallId.value,
      })
      .catch(() => {});

    await createOffer();

    addCallLogMessage({ call_type: 'voice', status: 'started', atIso: new Date().toISOString() });
  };

  const startVideoCall = async () => {
    if (!repo.activeUser) return;

    peerSide.value = 'caller';
    callType.value = 'video';
    activeCallId.value = uuidv4();
    callPeerUserId.value = repo.activeUser.id;
    callConversationId.value = repo.activeConversationId;
    callStatus.value = 'ringing';
    videoStatus.value = 'ringing';
    videoCallOpen.value = true;

    await startRingtone();

    // ✅ permission + cam+mic
    await getMedia({ video: true });
    attachLocalTracks();

    await nextTick();
    videoDialogRef.value?.setLocalStream?.(localStream.value);

    await repo
      .startCall({
        type: 'video',
        toUserId: repo.activeUser.id,
        conversationId: repo.activeConversationId,
        call_id: activeCallId.value,
      })
      .catch(() => {});

    await createOffer();

    addCallLogMessage({ call_type: 'video', status: 'started', atIso: new Date().toISOString() });
  };

  repo.onCallIncoming = async (call) => {
    activeCallId.value = call.call_id;
    callType.value = call.call_type;
    peerSide.value = 'receiver';

    // ✅ IMPORTANT: store who is calling + which conversation
    callPeerUserId.value = call.from_user_id;
    callConversationId.value = call.conversation_id;

    // optional: show correct user in dialog
    const u = repo.users.find((x) => String(x.id) === String(call.from_user_id));
    if (u) repo.activeUser = u;
    repo.activeConversationId = call.conversation_id;

    if (call.call_type === 'video') {
      videoCallOpen.value = true;
      videoStatus.value = 'ringing';
    } else {
      voiceCallOpen.value = true;
      voiceStatus.value = 'ringing';
    }

    await startRingtone();
    await flushSignals(activeCallId.value);
  };

  repo.onCallSignal = async (call) => {
    if (!call?.call_id) return;

    // ✅ if incoming not processed yet, queue
    if (!activeCallId.value) {
      queueSignal(call);
      return;
    }

    if (call.call_id !== activeCallId.value) {
      queueSignal(call);
      return;
    }

    await handleCallSignalNow(call);
  };

  repo.onCallEnded = async (call) => {
    if (!call?.call_id || call.call_id !== activeCallId.value) return;
    await endCall(call.status || 'ended', true);
  };

  const acceptVideo = async () => {
    stopRingtone();

    receiverAccepted.value = true;
    receiverAcceptedAt.value = 'video';

    videoStatus.value = 'connected';
    callStatus.value = 'connected';
    callStartedAt.value = new Date().toISOString();

    await getMedia({ video: true });
    attachLocalTracks();

    await nextTick();
    videoDialogRef.value?.setLocalStream?.(localStream.value);

    // If offer already came, answer now
    if (pendingOfferSdp.value) {
      await acceptOfferCreateAnswer(pendingOfferSdp.value);
      pendingOfferSdp.value = null;
    }
  };

  const acceptVoice = async () => {
    stopRingtone();

    receiverAccepted.value = true;
    receiverAcceptedAt.value = 'voice';

    voiceStatus.value = 'connected';
    callStatus.value = 'connected';
    callStartedAt.value = new Date().toISOString();

    await getMedia({ video: false });
    attachLocalTracks();

    if (pendingOfferSdp.value) {
      await acceptOfferCreateAnswer(pendingOfferSdp.value);
      pendingOfferSdp.value = null;
    }
    // ✅ now we know the callId, process any signals that arrived early
    await flushSignals(activeCallId.value);
  };

  const endVoice = () => endCall('ended');
  const endVideo = () => endCall('ended');

  const endCall = async (reason = 'ended', fromRemote = false) => {
    stopRingtone();

    const durationSec = secondsSinceStart();
    const duration = callStartedAt.value ? fmtDuration(durationSec) : '00:00';

    voiceCallOpen.value = false;
    videoCallOpen.value = false;

    voiceStatus.value = 'ended';
    videoStatus.value = 'ended';
    callStatus.value = reason;

    cleanup(); // ✅ stop mic/cam + close peer

    addCallLogMessage({
      call_type: callType.value || 'voice',
      status: reason,
      duration,
      atIso: new Date().toISOString(),
    });

    if (!fromRemote && callPeerUserId.value && callConversationId.value && activeCallId.value) {
      await repo
        .endCall({
          toUserId: callPeerUserId.value,
          conversationId: callConversationId.value,
          callId: activeCallId.value,
          type: callType.value,
          status: reason,
          duration_seconds: durationSec,
        })
        .catch(() => {});
    }

    callStartedAt.value = null;
    callType.value = null;
    activeCallId.value = null;
    pendingOfferSdp.value = null;
  };

  const voiceStatus = ref('idle'); // ringing|connected|ended
  const videoStatus = ref('idle');

  const {
    localStream,
    remoteStream,
    getMedia,
    attachLocalTracks,
    createOffer,
    acceptOfferCreateAnswer,
    applyAnswer,
    addIce,
    cleanup,
  } = useWebRtcCall({
    onSendSignal: async (signal) => {
      if (!callConversationId.value || !callPeerUserId.value || !activeCallId.value) return;

      await repo
        .sendCallSignal({
          toUserId: callPeerUserId.value,
          conversationId: callConversationId.value,
          callId: activeCallId.value,
          callType: callType.value,
          signal,
        })
        .catch(() => {});
    },
  });

  const toggleMute = () => {
    muted.value = !muted.value;
    localStream.value?.getAudioTracks()?.forEach((t) => (t.enabled = !muted.value));
  };

  const toggleCamera = () => {
    cameraOff.value = !cameraOff.value;
    localStream.value?.getVideoTracks()?.forEach((t) => (t.enabled = !cameraOff.value));
  };

  const unlockRingtone = () => {
    ringAudio
      .play()
      .then(() => {
        ringAudio.pause();
        ringAudio.currentTime = 0;
      })
      .catch(() => {});
  };

  onMounted(() => {
    window.addEventListener('click', unlockRingtone, { once: true });
    window.addEventListener('keydown', unlockRingtone, { once: true });
  });

  /* =========================================================
     ✅ LOCAL i18n (ONLY inside this menu)
     - does NOT touch global app locale
     - RTL for fa/ps, LTR for en
  ========================================================= */
  const chatLocale = ref('en');
  const chatDir = computed(() =>
    chatLocale.value === 'fa' || chatLocale.value === 'ps' ? 'rtl' : 'ltr'
  );

  const M = {
    en: {
      'chat.title': 'Chat',
      'chat.subtitle': 'Start a conversation',
      'chat.language': 'Language',
      'chat.search': 'Search',
      'chat.deleteAllChats': 'Delete all chats',
      'chat.markAllRead': 'Mark all as read',
      'chat.refresh': 'Refresh',
      'chat.close': 'Close',
      'chat.searchContacts': 'Search contacts…',
      'chat.messageDeleted': 'This message was deleted',
      'chat.user': 'User',
      'chat.replyingTo': 'Replying to {name}',
      'chat.attach': 'Attach',
      'chat.images': 'Images',
      'chat.videos': 'Videos',
      'chat.docs': 'Docs',
      'chat.voices': 'Voices',
      'chat.typeReply': 'Type reply…',
      'chat.typeMessage': 'Type a message…',
      'chat.send': 'Send',
      'chat.welcome': 'Welcome',
      'chat.selectContact': 'Select a contact to start chatting',
      'chat.confirmDeleteOne': 'Delete this message?',
      'chat.confirmDeleteAll': 'Delete ALL chats?',
      'chat.noMessages': 'No messages',
      'chat.justNow': 'Just now',
    },
    fa: {
      'chat.title': 'چت',
      'chat.subtitle': 'گفتگو را شروع کنید',
      'chat.language': 'زبان',
      'chat.search': 'جستجو',
      'chat.deleteAllChats': 'حذف تمام چت‌ها',
      'chat.markAllRead': 'همه را خوانده‌شده کن',
      'chat.refresh': 'تازه‌سازی',
      'chat.close': 'بستن',
      'chat.searchContacts': 'جستجوی مخاطبین…',
      'chat.messageDeleted': 'این پیام حذف شد',
      'chat.user': 'کاربر',
      'chat.replyingTo': 'پاسخ به {name}',
      'chat.attach': 'ضمیمه',
      'chat.images': 'تصاویر',
      'chat.videos': 'ویدیوها',
      'chat.docs': 'اسناد',
      'chat.voices': 'صداها',
      'chat.typeReply': 'پاسخ را بنویسید…',
      'chat.typeMessage': 'پیام بنویسید…',
      'chat.send': 'ارسال',
      'chat.welcome': 'خوش آمدید',
      'chat.selectContact': 'برای شروع چت یک مخاطب را انتخاب کنید',
      'chat.confirmDeleteOne': 'این پیام حذف شود؟',
      'chat.confirmDeleteAll': 'همه چت‌ها حذف شود؟',
      'chat.noMessages': 'پیامی نیست',
      'chat.justNow': 'همین حالا',
    },
    ps: {
      'chat.title': 'چټ',
      'chat.subtitle': 'خبرې اترې پیل کړئ',
      'chat.language': 'ژبه',
      'chat.search': 'لټون',
      'chat.deleteAllChats': 'ټول چټونه ړنګول',
      'chat.markAllRead': 'ټول لوستل شوي کړه',
      'chat.refresh': 'تازه کول',
      'chat.close': 'بندول',
      'chat.searchContacts': 'اړيکې ولټوئ…',
      'chat.messageDeleted': 'دا پیغام ړنګ شوی',
      'chat.user': 'کارن',
      'chat.replyingTo': '{name} ته ځواب',
      'chat.attach': 'ضمیمه',
      'chat.images': 'انځورونه',
      'chat.videos': 'ویډیوګانې',
      'chat.docs': 'سندونه',
      'chat.voices': 'غږونه',
      'chat.typeReply': 'ځواب ولیکئ…',
      'chat.typeMessage': 'پیغام ولیکئ…',
      'chat.send': 'لېږل',
      'chat.welcome': 'ښه راغلاست',
      'chat.selectContact': 'د چټ پیل لپاره یو کس وټاکئ',
      'chat.confirmDeleteOne': 'دا پیغام ړنګ شي؟',
      'chat.confirmDeleteAll': 'ټول چټونه ړنګ شي؟',
      'chat.noMessages': 'پیغام نشته',
      'chat.justNow': 'همدا اوس',
    },
  };

  const tr = (key, vars = {}) => {
    const dict = M[chatLocale.value] || M.en;
    let s = dict[key] ?? M.en[key] ?? key;
    for (const [k, v] of Object.entries(vars)) {
      s = s.replaceAll(`{${k}}`, String(v));
    }
    return s;
  };

  const setChatLocale = (loc) => {
    chatLocale.value = loc === 'fa' || loc === 'ps' || loc === 'en' ? loc : 'en';
  };

  /* ========================================================= */

  const menuOpen = ref(false);
  const searchQuery = ref('');
  const draft = ref('');
  const messagesBox = ref(null);
  const messageInput = ref(null);

  const replyToMessage = ref(null);

  const attachOpen = ref(false);
  const searchOpen = ref(false);
  const voiceRef = ref(null);
  const voiceRecording = ref(false);
  const onVoiceReady = (file) => {
    if (!file) return;
    attachments.voices = [...attachments.voices, file]; // ✅ keep it in attachments preview
  };
  // reactive attachments (never undefined in template)
  const attachments = reactive({ images: [], videos: [], docs: [], voices: [] });

  const searchInlineRef = ref(null);

  const toggleSearch = async () => {
    searchOpen.value = !searchOpen.value;
    if (searchOpen.value) {
      await nextTick();
      searchInlineRef.value?.focus?.();
    }
  };

  watch(
    () => repo.activeConversationId,
    () => {
      searchOpen.value = false;
    }
  );

  const setAttachments = (v) => {
    attachments.images = Array.isArray(v?.images) ? [...v.images] : [];
    attachments.videos = Array.isArray(v?.videos) ? [...v.videos] : [];
    attachments.docs = Array.isArray(v?.docs) ? [...v.docs] : [];
    attachments.voices = Array.isArray(v?.voices) ? [...v.voices] : [];
  };

  const clearAttachments = () => {
    attachments.images = [];
    attachments.videos = [];
    attachments.docs = [];
    attachments.voices = [];
  };

  const hasPendingAttachments = computed(() => {
    return (
      (attachments.images?.length || 0) +
        (attachments.videos?.length || 0) +
        (attachments.docs?.length || 0) +
        (attachments.voices?.length || 0) >
      0
    );
  });

  // object url cache
  const blobUrlMap = new Map();

  const makeObjectUrl = (file) => {
    if (!file) return '';
    if (blobUrlMap.has(file)) return blobUrlMap.get(file);
    const url = URL.createObjectURL(file);
    blobUrlMap.set(file, url);
    return url;
  };

  const removeAttach = (key, idx) => {
    const arr = attachments?.[key] || [];
    const file = arr[idx];

    if (file && blobUrlMap.has(file)) {
      try {
        URL.revokeObjectURL(blobUrlMap.get(file));
      } catch {}
      blobUrlMap.delete(file);
    }

    arr.splice(idx, 1);
    attachments[key] = [...arr];
  };

  onBeforeUnmount(() => {
    for (const url of blobUrlMap.values()) {
      try {
        URL.revokeObjectURL(url);
      } catch {}
    }
    blobUrlMap.clear();
  });

  const filteredUsers = computed(() => {
    const term = searchQuery.value.trim().toLowerCase();
    if (!term) return repo.users;
    return repo.users.filter(
      (u) =>
        (u.name || '').toLowerCase().includes(term) ||
        (u.email || '').toLowerCase().includes(term) ||
        (u.username || '').toLowerCase().includes(term)
    );
  });

  const unreadCount = computed(() =>
    repo.conversations.reduce((t, c) => t + (c.unread_count || 0), 0)
  );

  const canSend = computed(() => {
    const hasText = !!draft.value.trim();
    const hasFiles = hasPendingAttachments.value;
    return hasText || hasFiles || voiceRecording.value;
  });

  const groupedMessages = computed(() => {
    const groups = [];
    let current = [];
    let lastSenderId = null;
    let lastTime = null;

    repo.messages.forEach((m, idx) => {
      const t = dayjs(m.created_at);
      const diff = lastTime ? Math.abs(t.diff(lastTime, 'minute')) : 30;
      if (m.sender?.id !== lastSenderId || diff > 5 || idx === 0) {
        if (current.length) groups.push([...current]);
        current = [m];
      } else current.push(m);

      lastSenderId = m.sender?.id;
      lastTime = t;
    });

    if (current.length) groups.push(current);
    return groups;
  });

  const onActivatorClick = async () => {
    try {
      await unlockRingtone(); // ✅ unlock sound on user click
      await repo.bootstrap();
      await repo.initBadge();
    } catch {}
  };

  watch(menuOpen, async (open) => {
    if (open) {
      await repo.bootstrap();
      await nextTick();
      scrollToBottom();
    } else {
      if (currentChannelKey && window.Echo) {
        try {
          window.Echo.leave(currentChannelKey);
        } catch {}
        currentChannelKey = null;
      }

      repo.closeChatSession();
      searchQuery.value = '';
      draft.value = '';
      replyToMessage.value = null;
      clearAttachments();
    }
  });

  const selectUser = async (user) => {
    // ✅ leave old channel before switching
    if (currentChannelKey && window.Echo) {
      try {
        window.Echo.leave(currentChannelKey);
      } catch {}
      currentChannelKey = null;
    }

    await repo.openConversationWith(user);

    // ✅ set the new channel key
    currentChannelKey = `conversation.${repo.activeConversationId}`;

    await nextTick();
    scrollToBottom();
  };

  const send = async () => {
    if (!repo.activeUser) return;

    // ✅ If recording, finalize first so it becomes a File and goes to attachments
    if (voiceRecording.value && voiceRef.value?.finalize) {
      const blobOrFile = await voiceRef.value.finalize();

      if (blobOrFile) {
        const file =
          blobOrFile instanceof File
            ? blobOrFile
            : new File([blobOrFile], `voice_${Date.now()}.webm`, {
                type: blobOrFile.type || 'audio/webm',
              });

        // ✅ Replace instead of append (prevents duplicate)
        attachments.voices = [file];
      }
    }

    // ✅ after finalize voiceRecording becomes false, so check again
    const hasText = !!draft.value.trim();
    const hasFiles = hasPendingAttachments.value;
    if (!hasText && !hasFiles) return;

    const body = draft.value.trim();
    const replyToId = replyToMessage.value?.id || null;

    const files = {
      images: [...attachments.images],
      videos: [...attachments.videos],
      docs: [...attachments.docs],
      voices: [...attachments.voices],
    };

    // ✅ DEBUG (important)
    console.log(
      'SENDING VOICES:',
      files.voices.map((f) => ({
        name: f.name,
        type: f.type,
        size: f.size,
      }))
    );

    const tmpId = `tmp-${Date.now()}-${Math.random().toString(16).slice(2)}`;
    const nowIso = new Date().toISOString();

    const optimistic = {
      id: tmpId,
      conversation_id: repo.activeConversationId,
      body,
      created_at: nowIso,
      deleted_at: null,
      seen_at: null,
      sender: repo.currentUser ? { ...repo.currentUser } : { id: repo.currentUser?.id },
      replyTo: replyToMessage.value || null,
      attachments: [], // (backend will return real attachments)
    };

    repo.messages.push(optimistic);

    // ✅ reset UI after pushing optimistic message
    draft.value = '';
    replyToMessage.value = null;
    clearAttachments();

    await nextTick();
    scrollToBottom();

    try {
      const createdRes = await repo.sendMessageForm({
        body,
        replyToId,
        receiverId: repo.activeUser?.id,
        conversationId: repo.activeConversationId,
        attachments: files,
      });

      const created = createdRes?.message ?? createdRes; // support both shapes

      if (created && created.id) {
        // if conversation was created now, set it
        if (!repo.activeConversationId && created.conversation_id) {
          repo.activeConversationId = created.conversation_id;
          repo.subscribeToConversationChannel(repo.activeConversationId);
        }

        // update tmp conversation_id (optional)
        const tmpIndex = repo.messages.findIndex((m) => String(m.id) === String(tmpId));
        if (tmpIndex !== -1) {
          repo.messages[tmpIndex] = {
            ...repo.messages[tmpIndex],
            conversation_id: created.conversation_id || repo.activeConversationId,
          };
        }

        // ✅ remove duplicates of the real id, but KEEP the tmp one
        repo.messages = repo.messages.filter(
          (m) => String(m.id) === String(tmpId) || String(m.id) !== String(created.id)
        );

        // ✅ replace tmp with real (or push if tmp not found)
        const idx = repo.messages.findIndex((m) => String(m.id) === String(tmpId));
        if (idx !== -1) repo.messages.splice(idx, 1, created);
        else repo.messages.push(created);
      }
    } catch (e) {
      console.error('SEND FAILED:', e);

      // ✅ remove optimistic if failed
      repo.messages = repo.messages.filter((m) => String(m.id) !== String(tmpId));
    }
  };

  const startReply = (m) => {
    replyToMessage.value = m;
    messageInput.value?.focus?.();
  };

  const cancelReply = () => {
    replyToMessage.value = null;
  };

  const deleteOne = async (m) => {
    if (!confirm(tr('chat.confirmDeleteOne'))) return;
    await repo.deleteMessage(m.id);
  };

  const deleteAllChats = async () => {
    if (!confirm(tr('chat.confirmDeleteAll'))) return;
    await repo.deleteAllChats();
  };

  const markAllAsRead = async () => {
    await repo.markAllAsRead();
  };

  const handleScroll = () => {};

  const scrollToBottom = () => {
    if (!messagesBox.value) return;
    requestAnimationFrame(() => {
      messagesBox.value?.scrollTo({ top: messagesBox.value.scrollHeight, behavior: 'smooth' });
    });
  };

  const scrollToMessage = (messageId) => {
    const el = document.querySelector(`[data-message-id="${messageId}"]`);
    if (el) {
      el.scrollIntoView({ behavior: 'smooth', block: 'center' });
      el.classList.add('highlighted');
      setTimeout(() => el.classList.remove('highlighted'), 2000);
    }
  };

  const doSearch = async (q) => {
    return await repo.searchInConversation(q);
  };

  const truncateText = (text, len) => {
    if (!text) return '';
    if (text.length <= len) return text;
    return text.substring(0, len) + '...';
  };

  // ✅ IMPORTANT: do NOT call dayjs.locale() globally (so it won't affect outside menu)
  const formatTime = (iso) => (iso ? dayjs(iso).locale(chatLocale.value).format('HH:mm') : '');

  const formatRelativeTime = (iso) => {
    if (!iso) return '';
    const now = dayjs();
    const t = dayjs(iso);
    if (now.diff(t, 'minute') < 1) return tr('chat.justNow');
    return dayjs(iso).locale(chatLocale.value).fromNow();
  };

  const getLastMessageTime = (user) => {
    const c = repo.conversations.find((x) => x.other_user?.id === user.id);
    return c?.last_message?.created_at;
  };

  const getLastMessagePreview = (user) => {
    const c = repo.conversations.find((x) => x.other_user?.id === user.id);
    const text = c?.last_message?.body || '';
    return text ? (text.length > 35 ? text.slice(0, 35) + '...' : text) : tr('chat.noMessages');
  };

  const conversationUnreadByUser = (userId) => {
    const c = repo.conversations.find((x) => x.other_user?.id === userId);
    return c?.unread_count || 0;
  };

  watch(remoteStream, async (s) => {
    await nextTick();
    if (callType.value === 'video') {
      videoDialogRef.value?.setRemoteStream?.(s);
    } else {
      voiceDialogRef.value?.setRemoteStream?.(s);
    }
  });

  watch(localStream, async (s) => {
    await nextTick();
    if (callType.value === 'video') {
      videoDialogRef.value?.setLocalStream?.(s);
    }
  });

  onMounted(() => {
    // keep empty; we intentionally avoid global locale changes
  });
</script>

<style scoped>
  /* keep your existing styles exactly (unchanged)
     I am not repeating everything again to avoid noise.
     Your preview styles + attach styles are already good.
  */

  .composer-previews {
    width: 100%;
    background: #f8fafc;
    border: 1px solid rgba(0, 0, 0, 0.06);
    border-radius: 14px;
    padding: 10px 12px;
    display: grid;
    gap: 10px;
  }
  .preview-row {
    display: grid;
    gap: 8px;
  }
  .preview-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 800;
    font-size: 12px;
    color: #334155;
  }
  .preview-items {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
  }
  .thumb-wrap {
    position: relative;
    width: 64px;
    height: 64px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.08);
    background: white;
  }
  .thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }
  .thumb-x {
    position: absolute;
    top: -6px;
    right: -6px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 999px;
  }
  .preview-chips {
    display: flex;
    flex-wrap: wrap;
  }

  .attach-list {
    margin-top: 10px;
    display: grid;
    gap: 10px;
  }
  .attach-img {
    width: 220px;
    max-width: 100%;
    border-radius: 12px;
    border: 1px solid rgba(0, 0, 0, 0.08);
  }
  .attach-video {
    width: 320px;
    max-width: 100%;
    border-radius: 12px;
  }
  .attach-audio {
    width: 320px;
    max-width: 100%;
  }
  .attach-doc {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border-radius: 12px;
    background: rgba(102, 126, 234, 0.08);
    border: 1px solid rgba(102, 126, 234, 0.2);
    text-decoration: none;
    color: #0f172a;
    font-weight: 700;
  }
  .deleted-text {
    color: rgba(255, 255, 255, 0.85);
    font-style: italic;
  }

  .attach-list {
    margin-top: 10px;
    display: grid;
    gap: 10px;
  }
  .attach-img {
    width: 220px;
    max-width: 100%;
    border-radius: 12px;
    border: 1px solid rgba(0, 0, 0, 0.08);
  }
  .attach-video {
    width: 320px;
    max-width: 100%;
    border-radius: 12px;
  }
  .attach-audio {
    width: 320px;
    max-width: 100%;
  }
  .attach-doc {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border-radius: 12px;
    background: rgba(102, 126, 234, 0.08);
    border: 1px solid rgba(102, 126, 234, 0.2);
    text-decoration: none;
    color: #0f172a;
    font-weight: 700;
  }
  .deleted-text {
    color: rgba(255, 255, 255, 0.85);
    font-style: italic;
  }

  /* ✅ SAME STYLE YOU SENT (UNCHANGED) */
  .chat-card,
  .chat-card * {
    font-family: inherit !important;
  }
  .chat-card {
    height: 720px;
    width: 900px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 10px 20px rgba(0, 0, 0, 0.1);
    background: #ffffff;
  }
  .chat-header {
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    flex-shrink: 0;
  }
  .header-icon {
    background: rgba(255, 255, 255, 0.2);
    padding: 10px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(10px);
  }
  .header-content {
    margin-left: 12px;
  }
  [dir='rtl'] .header-content {
    margin-left: 0;
    margin-right: 12px;
  }
  .title {
    font-weight: 700;
    font-size: 20px;
    letter-spacing: 0.3px;
  }
  .subtitle {
    font-size: 12px;
    opacity: 0.9;
    margin-top: 2px;
  }
  .header-actions {
    display: flex;
    gap: 4px;
    align-items: center;
  }
  .header-actions .action-btn {
    color: white;
  }
  .language-list {
    max-height: 300px;
    overflow-y: auto;
  }
  .language-list .active {
    background: rgba(102, 126, 234, 0.1);
  }
  .chat-body {
    flex: 1;
    display: grid;
    grid-template-columns: 320px 1fr;
    height: calc(100% - 81px);
    background: #f8fafc;
    overflow: hidden;
  }
  .chat-left {
    width: 320px;
    min-width: 320px;
    max-width: 320px;
    border-right: 1px solid rgba(0, 0, 0, 0.08);
    display: flex;
    flex-direction: column;
    background: white;
    overflow: hidden;
  }
  .search-container {
    padding: 16px;
    background: white;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    flex-shrink: 0;
  }
  .users-search :deep(.v-field) {
    border-radius: 12px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
    height: 44px;
  }
  .users-search :deep(.v-field):hover,
  .users-search :deep(.v-field):focus-within {
    border-color: #667eea;
    background: white;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
  }
  .search-btn {
    margin-right: 4px;
  }
  [dir='rtl'] .search-btn {
    margin-right: 0;
    margin-left: 4px;
  }
  .users-scroll {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 8px 12px;
  }
  .user-item {
    margin: 4px 0;
    padding: 12px;
    border-radius: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
    border: 2px solid transparent;
  }
  .user-item:hover {
    background: #f8fafc;
    transform: translateX(2px);
  }
  [dir='rtl'] .user-item:hover {
    transform: translateX(-2px);
  }
  .user-item-active {
    background: #f1f5f9;
    border-color: #667eea;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
  }
  .avatar-wrap {
    position: relative;
  }
  .avatar {
    position: relative;
  }
  .status-dot-avatar {
    position: absolute;
    bottom: 12px;
    right: 0px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
    background: #94a3b8;
    z-index: 4;
  }
  .status-dot-avatar.online {
    background: #10b981;
  }
  [dir='rtl'] .status-dot-avatar {
    right: auto;
    left: 0px;
  }
  .user-info {
    flex: 1;
    min-width: 0;
    margin-left: 12px;
  }
  [dir='rtl'] .user-info {
    margin-left: 0;
    margin-right: 12px;
  }
  .user-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 6px;
  }
  .name-section {
    display: flex;
    align-items: center;
    gap: 6px;
    flex: 1;
    min-width: 0;
  }
  .name {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .mute-icon {
    flex-shrink: 0;
  }
  .time {
    font-size: 11px;
    color: #94a3b8;
    font-weight: 500;
    white-space: nowrap;
    margin-left: 4px;
  }
  [dir='rtl'] .time {
    margin-left: 0;
    margin-right: 4px;
  }
  .message-preview {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
  }
  .message-text {
    font-size: 13px;
    color: #64748b;
    font-weight: 400;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
    min-width: 0;
  }
  .unread-indicator {
    background: #667eea;
    color: white;
    font-size: 11px;
    font-weight: 700;
    min-width: 20px;
    height: 20px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 6px;
    flex-shrink: 0;
  }
  .empty-left {
    padding: 60px 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    align-items: center;
    text-align: center;
  }
  .empty-text {
    font-size: 14px;
    color: #94a3b8;
    font-weight: 500;
  }
  .chat-right {
    min-width: 0;
    display: flex;
    flex-direction: column;
    background: white;
    position: relative;
    overflow: hidden;
  }
  .chat-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
    background: #f8fafc;
  }
  .empty-content {
    text-align: center;
    max-width: 400px;
  }
  .chat-right-header {
    padding: 16px 24px;
    background: white;
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .active-user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
  }
  .active-user-avatar {
    position: relative;
    flex-shrink: 0;
  }
  .active-status-dot {
    position: absolute;
    bottom: 3px;
    right: 4px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid white;
    background: #94a3b8;
    z-index: 2;
  }
  .active-status-dot.online {
    background: #10b981;
  }
  [dir='rtl'] .active-status-dot {
    right: auto;
    left: 0px;
  }
  .active-user-details {
    flex: 1;
    min-width: 0;
  }
  .active-user-name {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .messages-box {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 24px;
    background: #f8fafc;
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
  .message-wrapper {
    display: flex;
    gap: 8px;
    align-items: flex-end;
    max-width: 100%;
  }
  .message-wrapper.sent {
    margin-left: auto;
    flex-direction: row-reverse;
  }
  .message-wrapper.received {
    margin-right: auto;
  }
  .message-bubble-wrapper {
    display: flex;
    flex-direction: column;
    max-width: 70%;
  }
  .message-bubble {
    padding: 12px 16px;
    border-radius: 18px;
    position: relative;
    max-width: 100%;
    word-wrap: break-word;
    word-break: break-word;
  }
  .reply-quote {
    cursor: pointer;
    margin-bottom: 8px;
    padding: 8px 10px;
    border-radius: 12px;
    background: rgba(0, 0, 0, 0.06);
    border-left: 4px solid rgba(102, 126, 234, 0.9);
  }
  [dir='rtl'] .reply-quote {
    border-left: none;
    border-right: 4px solid rgba(102, 126, 234, 0.9);
  }
  .reply-quote-top {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
    opacity: 0.9;
  }
  .reply-quote-name {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .reply-quote-body {
    margin-top: 4px;
    font-size: 12px;
    opacity: 0.85;
    line-height: 1.35;
  }
  .message-wrapper.received .message-bubble {
    background: #f1f5f9;
    color: #0f172a;
    border-bottom-left-radius: 4px;
  }
  .message-wrapper.sent .message-bubble {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-bottom-right-radius: 4px;
  }
  .message-wrapper.sent .message-bubble,
  .message-wrapper.sent .message-text,
  .message-wrapper.sent .message-time {
    color: #ffffff !important;
  }
  .message-wrapper.sent .reply-quote {
    background: rgba(255, 255, 255, 0.14);
    border-left: 4px solid rgba(255, 255, 255, 0.7);
  }
  [dir='rtl'] .message-wrapper.sent .reply-quote {
    border-left: none;
    border-right: 4px solid rgba(255, 255, 255, 0.7);
  }
  .message-wrapper.sent .reply-quote-top,
  .message-wrapper.sent .reply-quote-body {
    color: #ffffff !important;
    opacity: 0.95;
  }
  .message-text {
    font-size: 14px;
    line-height: 1.5;
    font-weight: 400;
    white-space: pre-wrap;
  }
  .message-meta {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 4px;
  }
  .message-time {
    font-size: 11px;
    opacity: 0.85;
    font-weight: 500;
  }
  .message-status {
    display: flex;
    align-items: center;
    gap: 4px;
  }
  .reply-btn {
    opacity: 0;
    transition: opacity 0.2s ease;
  }
  .message-bubble:hover .reply-btn {
    opacity: 1;
  }
  .composer {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 24px;
    border-top: 1px solid rgba(0, 0, 0, 0.06);
    background: white;
    flex-shrink: 0;
  }
  .composer-input {
    flex: 1;
  }
  .composer-input :deep(.v-field) {
    border-radius: 24px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
  }
  .send-btn {
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
  }
  .thin-scroll::-webkit-scrollbar {
    width: 18px;
  }
  .thin-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
  }
  .thin-scroll::-webkit-scrollbar-track {
    background: transparent;
  }
  .message-wrapper.highlighted .message-bubble {
    animation: highlightPulse 2s ease;
  }
  .call-log {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 14px;
    background: rgba(0, 0, 0, 0.06);
    border: 1px solid rgba(0, 0, 0, 0.08);
  }
  .message-wrapper.sent .call-log {
    background: rgba(255, 255, 255, 0.14);
    border: 1px solid rgba(255, 255, 255, 0.2);
  }
  .call-log-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }
  .call-log-title {
    font-weight: 900;
    font-size: 13px;
  }
  .call-log-sub {
    font-size: 12px;
    opacity: 0.9;
  }

  @keyframes highlightPulse {
    0%,
    100% {
      box-shadow: none;
    }
    50% {
      box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
    }
  }

  @media (max-width: 900px) {
    .chat-card {
      width: 100% !important;
      height: 100vh;
      border-radius: 0;
    }
    .chat-body {
      grid-template-columns: 1fr;
      grid-template-rows: auto 1fr;
    }
    .chat-left {
      width: 100%;
      min-width: 100%;
      max-width: 100%;
    }
  }
</style>
