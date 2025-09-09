import React from 'react';
import { ChatInterface } from './components/ChatInterface';
import { useGemini } from './hooks/useGemini';
import './App.css';
import { ConnectionTester } from './components/ConnectionTester';

function App() {
  const { messages, isLoading, error, sendMessage, clearChat } = useGemini();

  return (
    <div className="App">
      <ChatInterface
        messages={messages}
        isLoading={isLoading}
        error={error}
        onSendMessage={sendMessage}
        onClearChat={clearChat}
      />
      <ConnectionTester />
    </div>
  );
}

export default App;