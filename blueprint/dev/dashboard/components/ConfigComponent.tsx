import React from 'react';

const ConfigComponent: React.FC = () => {
  return (
    <div class="p-4">
      <h3>Config Editor</h3>
      <p>Manage extension and server configuration</p>
      <button class="btn btn-primary mt-2">Edit Config</button>
    </div>
  );
};

export default ConfigComponent;