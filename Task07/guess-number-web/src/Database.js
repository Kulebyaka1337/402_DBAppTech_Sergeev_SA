// src/Database.js — работа с IndexedDB, без логики отображения

const DB_NAME = 'guess-number-db';
const DB_VERSION = 2;
const STORE_GAMES = 'games';

function openDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(DB_NAME, DB_VERSION);

    request.onupgradeneeded = () => {
      const db = request.result;
      let store;

      if (!db.objectStoreNames.contains(STORE_GAMES)) {
        store = db.createObjectStore(STORE_GAMES, {
          keyPath: 'id',
          autoIncrement: true
        });
      } else {
        store = request.transaction.objectStore(STORE_GAMES);
      }

      if (!store.indexNames.contains('byStartedAt')) {
        store.createIndex('byStartedAt', 'startedAt');
      }
      if (!store.indexNames.contains('byPlayerName')) {
        store.createIndex('byPlayerName', 'playerName');
      }
    };

    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

function getStore(mode = 'readonly') {
  return openDB().then((db) =>
    db.transaction(STORE_GAMES, mode).objectStore(STORE_GAMES)
  );
}

export async function saveGame(record) {
  const store = await getStore('readwrite');
  return new Promise((resolve, reject) => {
    const req = store.add(record); // id автоинкремент
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
  });
}

export async function getGameById(id) {
  const numId = Number(id);
  if (!Number.isFinite(numId)) return null;

  const store = await getStore('readonly');
  return new Promise((resolve, reject) => {
    const req = store.get(numId);
    req.onsuccess = () => resolve(req.result || null);
    req.onerror = () => reject(req.error);
  });
}

export async function getAllGames() {
  const store = await getStore('readonly');
  return new Promise((resolve, reject) => {
    const games = [];
    const source = store.indexNames.contains('byStartedAt')
      ? store.index('byStartedAt')
      : store;

    const req = source.openCursor(null, 'prev'); // новые сверху

    req.onsuccess = (e) => {
      const cursor = e.target.result;
      if (cursor) {
        games.push(cursor.value);
        cursor.continue();
      } else {
        resolve(games);
      }
    };
    req.onerror = () => reject(req.error);
  });
}

export async function clearAllGames() {
  const store = await getStore('readwrite');
  return new Promise((resolve, reject) => {
    const req = store.clear();
    req.onsuccess = () => resolve();
    req.onerror = () => reject(req.error);
  });
}

export async function resetDatabase() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.deleteDatabase(DB_NAME);

    request.onsuccess = async () => {
      try {
        await openDB();
        resolve();
      } catch (e) {
        reject(e);
      }
    };

    request.onerror = () => {
      reject(request.error);
    };

    request.onblocked = () => {
      console.warn(
        'Удаление БД заблокировано. Закройте другие вкладки/окна с этой страницей и попробуйте ещё раз.'
      );
    };
  });
}