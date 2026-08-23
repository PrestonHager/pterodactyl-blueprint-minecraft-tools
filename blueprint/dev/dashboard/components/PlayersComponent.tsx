import React from 'react';

const PlayersComponent: React.FC = () => {
  return (
    <div class="p-4">
      <h3>Player & Whitelist</h3>
      <p>Manage player whitelist and permissions</p>
      <button class="btn btn-primary mt-2">Manage</button>
    </div>
  );
};

export default PlayersComponent;