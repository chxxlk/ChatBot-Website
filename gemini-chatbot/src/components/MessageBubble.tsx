import React from 'react';
import type { Message } from '../types';

interface MessageBubbleProps {
  message: Message;
  isWelcome?: boolean;
}

export const MessageBubble: React.FC<MessageBubbleProps> = ({ message, isWelcome = false }) => {
  const isUser = message.sender === 'user';
  
  return (
    <div className={`flex ${isUser ? 'justify-end' : 'justify-start'} animate-fade-in`}>
      <div
        className={`my-2 max-w-xs md:max-w-md lg:max-w-lg px-5 py-3 rounded-lg transition-all duration-200 hover:scale-[1.02] ${
          isWelcome
            ? 'bg-gradient-to-r from-blue-100 to-purple-100 border-2 border-blue-200/50 shadow-lg rounded-3xl'
            : isUser
            ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg rounded-3xl'
            : 'bg-white/90 backdrop-blur-md border border-gray-200/60 shadow-lg rounded-3xl'
        }`}
      >
        {isWelcome && (
          <div className="flex items-center mb-3">
            <div className="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-2xl flex items-center justify-center mr-3">
              <span className="text-white text-sm font-bold">F</span>
            </div>
            <div>
              <span className="text-sm font-semibold text-blue-600">Fernando</span>
              <span className="text-xs text-gray-500 ml-2">• Asisten Virtual</span>
            </div>
          </div>
        )}
        
        <p className="whitespace-pre-wrap leading-relaxed text-gray-800">
          {message.text}
        </p>
        
        <div className="flex justify-between items-center mt-3 pt-2 border-t border-white/20">
          <span className={`text-xs ${isUser ? 'text-blue-100' : 'text-gray-500'}`}>
            {message.timestamp.toLocaleTimeString([], { 
              hour: '2-digit', 
              minute: '2-digit' 
            })}
          </span>
          {!isUser && message.source && message.source !== 'system' && (
            <span className={`text-xs ${isUser ? 'text-blue-100' : 'text-gray-400'}`}>
              {message.source === 'database' ? '📊 Database' : '🤖 AI'}
            </span>
          )}
        </div>
      </div>
    </div>
  );
};