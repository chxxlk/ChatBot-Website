import React, { useRef, useEffect } from 'react';
import { MessageBubble } from './MessageBubble';
import { InputArea } from './InputArea';
import type { Message } from '../types';

interface ChatInterfaceProps {
  messages: Message[];
  isLoading: boolean;
  error: string | null;
  onSendMessage: (message: string) => void;
  onClearChat: () => void;
}

export const ChatInterface: React.FC<ChatInterfaceProps> = ({
  messages,
  isLoading,
  error,
  onSendMessage,
  onClearChat,
}) => {
  const messagesEndRef = useRef<HTMLDivElement>(null);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  return (
    <div className="flex flex-col h-screen bg-gray-100">
      <div className="bg-white shadow-sm p-4 flex justify-between items-center">
        <h1 className="text-xl font-semibold">Gemini Chatbot</h1>
        <button
          onClick={onClearChat}
          className="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600"
        >
          Clear Chat
        </button>
      </div>

      <div className="flex-1 overflow-y-auto p-4">
        {messages.length === 0 && !isLoading && (
          <div className="text-center text-gray-500 mt-10">
            Send a message to start chatting with Gemini!
          </div>
        )}
        
        {messages.map((message) => (
          <MessageBubble key={message.id} message={message} />
        ))}
        
        {isLoading && (
          <div className="flex justify-start mb-4">
            <div className="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg">
              <div className="flex items-center">
                <div className="animate-pulse mr-2">•</div>
                <div className="animate-pulse mr-2">•</div>
                <div className="animate-pulse">•</div>
              </div>
            </div>
          </div>
        )}
        
        {error && (
          <div className="flex justify-center mb-4">
            <div className="bg-red-100 text-red-800 px-4 py-2 rounded-lg">
              Error: {error}
            </div>
          </div>
        )}
        
        <div ref={messagesEndRef} />
      </div>

      <InputArea onSendMessage={onSendMessage} isLoading={isLoading} />
    </div>
  );
};