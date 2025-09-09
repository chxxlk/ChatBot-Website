import React from 'react';
import type { Message } from '../types';

interface MessageBubbleProps {
  message: Message;
}

export const MessageBubble: React.FC<MessageBubbleProps> = ({ message }) => {
  const isUser = message.sender === 'user';
  
  return (
    <div className={`flex ${isUser ? 'justify-end' : 'justify-start'} mb-4`}>
      <div
        className={`max-w-xs md:max-w-md lg:max-w-lg px-4 py-2 rounded-lg ${
          isUser
            ? 'bg-blue-500 text-white'
            : 'bg-gray-200 text-gray-800'
        }`}
      >
        <p className="whitespace-pre-wrap">{message.text}</p>
        <span className="text-xs opacity-70 block mt-1">
          {message.timestamp.toLocaleTimeString()}
        </span>
      </div>
    </div>
  );
};