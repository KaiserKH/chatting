import { create } from 'zustand';
import api from '../api/client';
import { connectSocket, getSocket } from '../sockets/socket';

type ChatParticipant = { id: string; username: string; avatarUrl?: string | null; lastSeenAt?: string | null };

type ChatMessage = {
  id: string;
  conversationId: string;
  senderId: string;
  text?: string | null;
  type?: string;
  createdAt: string;
  sender?: { id: string; username: string; avatarUrl?: string | null };
};

type ChatState = {
  conversations: unknown[];
  activeConversationId: string | null;
  messages: ChatMessage[];
  participants: ChatParticipant[];
  isLoading: boolean;
  loadConversations: () => Promise<void>;
  openConversation: (peerId: string) => Promise<void>;
  loadMessages: (conversationId: string) => Promise<void>;
  sendMessage: (payload: { conversationId?: string; recipientId?: string; text?: string }) => Promise<void>;
  wireSocket: () => void;
};

export const useChatStore = create<ChatState>((set, get) => ({
  conversations: [],
  activeConversationId: null,
  messages: [],
  participants: [],
  isLoading: false,
  loadConversations: async () => {
    set({ isLoading: true });
    const response = await api.get('/chat/conversations');
    set({ conversations: response.data?.data ?? [], isLoading: false });
  },
  openConversation: async (peerId) => {
    const response = await api.get(`/chat/conversations/${peerId}`);
    const conversation = response.data?.data;
    set({ activeConversationId: conversation?.id ?? null });
    if (conversation?.id) {
      await get().loadMessages(conversation.id);
    }
  },
  loadMessages: async (conversationId) => {
    const response = await api.get(`/chat/conversations/${conversationId}/messages`);
    set({ messages: response.data?.data ?? [], activeConversationId: conversationId });
  },
  sendMessage: async (payload) => {
    await api.post('/chat/messages', payload);
  },
  wireSocket: () => {
    const socket = connectSocket();
    if (!socket) return;
    socket.off('message:new');
    socket.on('message:new', (message: ChatMessage) => {
      if (message.conversationId === get().activeConversationId) {
        set({ messages: [message, ...get().messages] });
      }
    });
    socket.off('typing:start');
    socket.on('typing:start', () => undefined);
  }
}));
