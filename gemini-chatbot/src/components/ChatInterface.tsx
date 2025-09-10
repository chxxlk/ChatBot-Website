import React, { useRef, useEffect, useState } from 'react';
import { MessageBubble } from './MessageBubble';
import { InputArea } from './InputArea';
import type { Message } from '../types';
import { getWelcomeMessage } from '../utils/api';

interface ChatInterfaceProps {
  messages: Message[];
  isLoading: boolean;
  error: string | null;
  onSendMessage: (message: string) => void;
  onClearChat: () => void;
  onClearError: () => void;
}

export const ChatInterface: React.FC<ChatInterfaceProps> = ({
  messages,
  isLoading,
  error,
  onSendMessage,
  onClearChat,
  onClearError,
}) => {
  const messagesEndRef = useRef<HTMLDivElement>(null);
  const [welcomeMessage, setWelcomeMessage] = useState<Message | null>(null);
  const [isInitialized, setIsInitialized] = useState(false);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  useEffect(() => {
    const initializeChatbot = async () => {
      if (messages.length === 0 && !isInitialized) {
        try {
          const data = await getWelcomeMessage();
          const welcomeMsg: Message = {
            id: 'welcome-' + Date.now(),
            text: data.data.message,
            sender: 'ai',
            timestamp: new Date(),
            source: 'system',
            isWelcome: true
          };
          setWelcomeMessage(welcomeMsg);
        } catch (error) {
          console.error('Failed to load welcome message:', error);
          // Fallback welcome message
          const fallbackMsg: Message = {
            id: 'welcome-fallback',
            text: 'Halo! 👋 Saya Fernando, Asisten Virtual dari Program Studi Teknologi Informasi UKSW. Ada yang bisa saya bantu?',
            sender: 'ai',
            timestamp: new Date(),
            source: 'system',
            isWelcome: true
          };
          setWelcomeMessage(fallbackMsg);
        } finally {
          setIsInitialized(true);
        }
      }
    };

    initializeChatbot();
  }, [messages.length, isInitialized]);

  const handleClearChat = () => {
    setWelcomeMessage(null);
    onClearChat();
  };

  const displayMessages = welcomeMessage ? [welcomeMessage, ...messages] : messages;

  return (
    <div className="flex flex-col h-screen bg-gray-100">
      {/* Header dengan identitas chatbot */}
      <div className="bg-white shadow-sm p-4 flex justify-between items-center border-b">
        <div className="flex items-center space-x-3">
          <div className="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
            <span className="text-white font-bold text-lg"></span>
          </div>
          <div>
            <h1 className="text-xl font-semibold text-gray-800">Fernando</h1>
            <p className="text-sm text-gray-600">Asisten Virtual TI UKSW</p>
          </div>
        </div>
        <button
          onClick={handleClearChat}
          className="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-sm"
          title="Bersihkan percakapan"
        >
          🔄 Reset
        </button>
      </div>

      {/* Area pesan */}
      <div className="flex-1 overflow-y-auto p-4 bg-white">
        {displayMessages.length === 0 && !isLoading && (
          <div className="text-center text-gray-500 mt-10">
            <div className="animate-pulse">Memuat percakapan...</div>
          </div>
        )}
        
        {displayMessages.map((message) => (
          <MessageBubble 
            key={message.id} 
            message={message}
            isWelcome={message.isWelcome}
          />
        ))}
        
        {isLoading && (
          <div className="flex justify-start mb-4">
            <div className="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg max-w-xs">
              <div className="flex items-center space-x-1">
                <div className="w-2 h-2 bg-gray-600 rounded-full animate-bounce"></div>
                <div className="w-2 h-2 bg-gray-600 rounded-full animate-bounce" style={{ animationDelay: '0.1s' }}></div>
                <div className="w-2 h-2 bg-gray-600 rounded-full animate-bounce" style={{ animationDelay: '0.2s' }}></div>
              </div>
            </div>
          </div>
        )}
        
        {error && (
          <div className="flex justify-center mb-4">
            <div className="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded-lg relative">
              <strong className="font-bold">Error: </strong>
              <span className="block sm:inline">{error}</span>
              <button
                onClick={onClearError}
                className="absolute top-0 right-0 mt-1 mr-2 text-red-800 hover:text-red-600"
              >
                ×
              </button>
            </div>
          </div>
        )}
        
        <div ref={messagesEndRef} />
      </div>

      {/* Input area */}
      <InputArea 
        onSendMessage={onSendMessage} 
        isLoading={isLoading}
        placeholder="Tanyakan tentang pengumuman, prodi, dosen, atau informasi kampus lainnya..."
      />

      {/* Footer dengan informasi chatbot */}
      <div className="bg-gray-800 text-white p-2 text-center text-xs">
        <p>🤖 Fernando - Asisten Virtual Program Studi Teknologi Informasi</p>
        <p>Universitas Kristen Satya Wacana</p>
      </div>
    </div>
  );
};