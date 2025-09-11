import React from 'react';
import { ChatInterface } from './ChatInterface';
import type { Message } from '../types';

interface ChatModalProps {
    isOpen: boolean;
    onClose: () => void;
    messages: Message[];
    isLoading: boolean;
    error: string | null;
    onSendMessage: (message: string) => void;
    onClearChat: () => void;
    onClearError: () => void;
}

export const ChatModal: React.FC<ChatModalProps> = ({
    isOpen,
    onClose,
    messages,
    isLoading,
    error,
    onSendMessage,
    onClearChat,
    onClearError,
}) => {
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-end justify-end p-4 sm:items-end sm:justify-right">
            {/* Backdrop */}
            <div
                className="fixed inset-0 bg-black/50 backdrop-blur-lg bg-opacity-50 transition-opacity"
                onClick={onClose}
            />

            {/* Modal */}
            <div className="relative bg-white rounded-lg shadow-2xl w-full max-w-md h-[600px] flex flex-col transform transition-all duration-300 ease-in-out">
                {/* Header */}
                <div className="flex items-center justify-between p-4 border-b border-gray-200 bg-gradient-to-r from-blue-500 to-purple-500 text-white rounded-t-lg">
                    <div className="flex items-center space-x-3">
                        <div className="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <span className="text-lg">🤖</span>
                        </div>
                        <div>
                            <h3 className="font-semibold">Fernando Assistant</h3>
                            <p className="text-xs text-white/80">Online • Siap membantu</p>
                        </div>
                    </div>

                    <button
                        onClick={onClose}
                        className="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 transition-colors flex items-center justify-center"
                    >
                        <span className="text-white">×</span>
                    </button>
                </div>

                {/* Chat Content */}
                <div className="flex-1 overflow-hidden">
                    <ChatInterface
                        messages={messages}
                        isLoading={isLoading}
                        error={error}
                        onSendMessage={onSendMessage}
                        onClearChat={onClearChat}
                        onClearError={onClearError}
                        isModal={true} // Tambahkan prop ini
                    />
                </div>
            </div>
        </div>
    );
};