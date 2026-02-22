import { ref } from 'vue';

export function useWebRtcCall({ onSendSignal }) {
  const pc = ref(null);
  const localStream = ref(null);
  const remoteStream = ref(null);

  // helpful debug state
  const connectionState = ref('new');
  const iceConnectionState = ref('new');
  const signalingState = ref('stable');

  // ICE candidates can arrive before remoteDescription => queue them
  const pendingIce = [];

  const iceConfig = {
    iceServers: [{ urls: ['stun:stun.l.google.com:19302'] }],
  };

  const createPeer = () => {
    if (pc.value) return pc.value;

    const peer = new RTCPeerConnection(iceConfig);
    remoteStream.value = new MediaStream();

    peer.ontrack = (e) => {
      if (!remoteStream.value) remoteStream.value = new MediaStream();
      remoteStream.value.addTrack(e.track);
    };

    peer.onicecandidate = (e) => {
      if (e.candidate) {
        onSendSignal({ type: 'ice', candidate: e.candidate });
      }
    };

    peer.onconnectionstatechange = () => {
      connectionState.value = peer.connectionState;
    };
    peer.oniceconnectionstatechange = () => {
      iceConnectionState.value = peer.iceConnectionState;
    };
    peer.onsignalingstatechange = () => {
      signalingState.value = peer.signalingState;
    };

    pc.value = peer;
    return peer;
  };

  const getMedia = async ({ video }) => {
    const stream = await navigator.mediaDevices.getUserMedia({
      audio: true,
      video: !!video,
    });
    localStream.value = stream;
    return stream;
    
  };

  const attachLocalTracks = () => {
    const peer = createPeer();
    if (!localStream.value) return;

    // prevent duplicate tracks if called twice
    const existingTrackIds = new Set(
      peer
        .getSenders()
        .map((s) => s.track?.id)
        .filter(Boolean)
    );

    localStream.value.getTracks().forEach((t) => {
      if (!existingTrackIds.has(t.id)) {
        peer.addTrack(t, localStream.value);
      }
    });
  };

  const setRemoteDescriptionSafely = async (sdp) => {
    const peer = createPeer();
    await peer.setRemoteDescription(sdp);

    // flush queued ICE now that remoteDescription exists
    while (pendingIce.length) {
      const c = pendingIce.shift();
      try {
        await peer.addIceCandidate(c);
      } catch (err) {
        console.warn('addIceCandidate failed (queued):', err);
      }
    }
  };

  const createOffer = async () => {
    const peer = createPeer();

    // IMPORTANT: add tracks BEFORE creating offer
    attachLocalTracks();

    const offer = await peer.createOffer();
    await peer.setLocalDescription(offer);

    onSendSignal({ type: 'offer', sdp: peer.localDescription });
  };

  const acceptOfferCreateAnswer = async (offerSdp) => {
    const peer = createPeer();

    // IMPORTANT: set remote offer first
    await setRemoteDescriptionSafely(offerSdp);

    // ensure we are sending our tracks
    attachLocalTracks();

    const answer = await peer.createAnswer();
    await peer.setLocalDescription(answer);

    onSendSignal({ type: 'answer', sdp: peer.localDescription });
  };

  const applyAnswer = async (answerSdp) => {
    await setRemoteDescriptionSafely(answerSdp);
  };

  const addIce = async (candidate) => {
    const peer = createPeer();
    const ice = new RTCIceCandidate(candidate);

    // if remoteDescription not set yet, queue it
    if (!peer.remoteDescription) {
      pendingIce.push(ice);
      return;
    }

    try {
      await peer.addIceCandidate(ice);
    } catch (err) {
      console.warn('addIceCandidate failed:', err);
    }
  };

  const cleanup = () => {
    try {
      const peer = pc.value;
      if (peer) {
        peer.getSenders().forEach((s) => {
          try {
            peer.removeTrack(s);
          } catch {}
        });
        peer.close();
      }
    } catch {}

    try {
      localStream.value?.getTracks?.().forEach((t) => t.stop());
    } catch {}

    pc.value = null;
    localStream.value = null;
    remoteStream.value = null;

    pendingIce.length = 0;

    connectionState.value = 'new';
    iceConnectionState.value = 'new';
    signalingState.value = 'stable';
  };

  return {
    pc,
    localStream,
    remoteStream,
    connectionState,
    iceConnectionState,
    signalingState,
    getMedia,
    attachLocalTracks,
    createOffer,
    acceptOfferCreateAnswer,
    applyAnswer,
    addIce,
    cleanup,
  };
}
