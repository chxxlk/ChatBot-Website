import { useState, useCallback } from 'react';
import { generateResponse } from '../utils/geminiClient';
import { sendChatMessage } from '../utils/api';
import type { Message, ChatState } from '../types';

export const useGemini = () => {
  const [state, setState] = useState<ChatState>({
    messages: [],
    isLoading: false,
    error: null,
  });

  const sendMessage = useCallback(async (message: string) => {
    const userMessage: Message = {
      id: Date.now().toString(),
      text: message,
      sender: 'user',
      timestamp: new Date(),
    };

    setState(prev => ({
      ...prev,
      isLoading: true,
      error: null,
      messages: [...prev.messages, userMessage],
    }));

    // try {
    //   const response = await generateResponse(message);

    //   const aiMessage: Message = {
    //     id: (Date.now() + 1).toString(),
    //     text: response,
    //     sender: 'ai',
    //     timestamp: new Date(),
    //   };

    //   setState(prev => ({
    //     ...prev,
    //     isLoading: false,
    //     messages: [...prev.messages, aiMessage],
    //   }));
    // } catch (error) {
    //   setState(prev => ({
    //     ...prev,
    //     isLoading: false,
    //     error: error instanceof Error ? error.message : 'An error occurred',
    //   }));
    // }

    try {
      // Use the Laravel backend API
      const response = await sendChatMessage(message);

      const aiMessage: Message = {
        id: (Date.now() + 1).toString(),
        text: response.response,
        sender: 'ai',
        timestamp: new Date(),
        // source: response.source // Add source information
      };

      setState(prev => ({
        ...prev,
        isLoading: false,
        messages: [...prev.messages, aiMessage],
      }));
    } catch (error: any) {
      setState(prev => ({
        ...prev,
        isLoading: false,
        error: error.message || "Failed to send message. Please try again.",
      }));
    }
  }, []);

  const clearChat = useCallback(() => {
    setState({
      messages: [],
      isLoading: false,
      error: null,
    });
  }, []);

  return {
    messages: state.messages,
    isLoading: state.isLoading,
    error: state.error,
    sendMessage,
    clearChat,
  };
};