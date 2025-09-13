import React, { useState } from 'react';
import { testConnection, testDatabase } from '../utils/api';

export const ConnectionTester: React.FC = () => {
  const [isTesting, setIsTesting] = useState(false);
  const [isTestingDb, setIsTestingDb] = useState(false);
  const [testResult, setTestResult] = useState<string>('');

  const handleTestConnection = async () => {
    setIsTesting(true);
    setTestResult('');
    
    try {
      const result = await testConnection();
      setTestResult(`✅ Success: ${JSON.stringify(result)}`);
    } catch (error: any) {
      setTestResult(`❌ Error: ${error.message}`);
    } finally {
      setIsTesting(false);
    }
  };

  const handleTestDatabase = async () => {
    setIsTestingDb(true);
    setTestResult('');
    
    try {
      const result = await testDatabase();
      setTestResult(`✅ Database Success: ${JSON.stringify(result)}`);
    } catch (error: any) {
      setTestResult(`❌ Database Error: ${error.message}`);
    } finally {
      setIsTestingDb(false);
    }
  };

  return (
    <div className="p-4 bg-yellow-50 border border-yellow-200 rounded mb-4">
      <h3 className="font-bold text-lg mb-2">Test Koneksi Backend (Laravel 12.x)</h3>
      
      <div className="flex space-x-2 mb-2">
        <button
          onClick={handleTestConnection}
          disabled={isTesting}
          className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50"
        >
          {isTesting ? 'Testing...' : 'Test Koneksi API'}
        </button>
        
        <button
          onClick={handleTestDatabase}
          disabled={isTestingDb}
          className="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:opacity-50"
        >
          {isTestingDb ? 'Testing...' : 'Test Database'}
        </button>
      </div>
      
      {testResult && (
        <div className="mt-2 p-2 bg-white rounded">
          <strong>Hasil:</strong> <span className={testResult.includes('❌') ? 'text-red-600' : 'text-green-600'}>{testResult}</span>
        </div>
      )}
      
      <div className="mt-2 text-sm">
        Pastikan backend Laravel 12.x berjalan di <code>http://localhost:8000</code>
      </div>
    </div>
  );
};