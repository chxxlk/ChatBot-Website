import { useState } from 'react';
import { ChatModal } from './components/ChatModal';
import { FloatingChatButton } from './components/FloatingChatButton';
import { useOpenRouter } from './hooks/useOpenRouter';
import './App.css';

function App() {
  const { messages, isLoading, error, sendMessage, clearChat, clearError } = useOpenRouter();
  const [isChatOpen, setIsChatOpen] = useState(false);

  const handleOpenChat = () => {
    setIsChatOpen(true);
  };

  const handleCloseChat = () => {
    setIsChatOpen(false);
  };

  return (
    <div className="App">
      {/* Floating Chat Button */}
      <FloatingChatButton onOpen={handleOpenChat} isOpen={isChatOpen} />

      {/* Chat Modal */}
      <ChatModal
        isOpen={isChatOpen}
        onClose={handleCloseChat}
        messages={messages}
        isLoading={isLoading}
        error={error}
        onSendMessage={sendMessage}
        onClearChat={clearChat}
        onClearError={clearError}
      />
    </div>
  );
}

export default App;