// import React from 'react';
import { ChatInterface } from './components/ChatInterface';
// import { ConnectionTester } from './components/ConnectionTester';
// import { DatabaseInfo } from './components/DatabaseInfo';
import { useGemini } from './hooks/useGemini';
import './App.css';

function App() {
  const { messages, isLoading, error, sendMessage, clearChat, clearError } = useGemini();

  return (
    <div className="App">
      <div className="container mx-auto p-4 max-w-4xl">
        {/* <ConnectionTester /> */}
        {/* <DatabaseInfo /> */}
        <ChatInterface
          messages={messages}
          isLoading={isLoading}
          error={error}
          onSendMessage={sendMessage}
          onClearChat={clearChat}
          onClearError={clearError}
        />
      </div>
    </div>
  );
}

export default App;