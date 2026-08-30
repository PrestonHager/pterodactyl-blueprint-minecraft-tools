import { createApp } from 'vue';
import './assets/main.scss';

export default {
  id: 'minecraft-tools',
  name: 'Minecraft Tools',
  version: '1.0.0',
  description: 'A Pterodactyl extension for Minecraft server utilities',
  
  install(app) {
    app.config.globalProperties.$minecraftTools = this;
  },
};

export function mountMinecraftTools(selector) {
  const app = createApp({});
  app.mount(selector);
  return app;
}