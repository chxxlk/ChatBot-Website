import React from 'react';
import type { Message } from '../types';

interface MessageBubbleProps {
  message: Message;
  isWelcome?: boolean;
}

export const MessageBubble: React.FC<MessageBubbleProps> = ({ message, isWelcome = false }) => {
  const isUser = message.sender === 'user';
  
  return (
    <div className={`flex ${isUser ? 'justify-end' : 'justify-start'} mb-4`}>
      <div
        className={`max-w-xs md:max-w-md lg:max-w-lg px-4 py-2 rounded-lg ${
          isWelcome
            ? 'bg-blue-100 border border-blue-300 text-blue-800' // Style khusus untuk welcome message
            : isUser
            ? 'bg-blue-500 text-white'
            : 'bg-gray-200 text-gray-800'
        }`}
      >        
        <p className="whitespace-pre-wrap">{message.text}</p>
        
        <div className="flex justify-between items-center mt-1">
          <span className="text-xs opacity-70">
            {message.timestamp.toLocaleTimeString([], { 
              hour: '2-digit', 
              minute: '2-digit' 
            })}
          </span>
          {!isUser && message.source && message.source !== 'system' && (
            <span className="text-xs opacity-70">
              Sumber: {message.source === 'database' ? '📊 Database' : '🤖 AI'}
            </span>
          )}
        </div>
      </div>
    </div>
  );
};