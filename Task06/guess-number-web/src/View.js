import { Game } from './Game.js';
import { formatDuration } from './Utils.js';

export class UI {
  constructor(){
    this.el = {
      min: document.getElementById('minValue'),
      max: document.getElementById('maxValue'),
      limitChk: document.getElementById('limitAttempts'),
      limitRow: document.getElementById('attemptLimitRow'),
      limitInput: document.getElementById('attemptLimit'),
      startBtn: document.getElementById('startBtn'),
      resetBtn: document.getElementById('resetBtn'),
      guessInput: document.getElementById('guessInput'),
      guessBtn: document.getElementById('guessBtn'),
      status: document.getElementById('status'),
      log: document.getElementById('logList'),
      statAttempts: document.getElementById('statAttempts'),
      statElapsed: document.getElementById('statElapsed'),
      statResult: document.getElementById('statResult'),
      playAgainBtn: document.getElementById('playAgainBtn'),
    };

    this.game = new Game();
    this.timerId = null;
    this._bind();
    this._init();
  }

  _bind(){
    this.el.limitChk.addEventListener('change', () => {
      this.el.limitRow.hidden = !this.el.limitChk.checked;
    });

    this.el.startBtn.addEventListener('click', () => this.start());
    this.el.resetBtn.addEventListener('click', () => this.reset());

    this.el.guessBtn.addEventListener('click', () => this.submitGuess());
    this.el.guessInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') this.submitGuess(); });

    this.el.playAgainBtn.addEventListener('click', () => this.start());
  }

  _init(){
    this._setStatus('Нажмите «Начать новую игру»');
    this.el.limitRow.hidden = true;
    this._enablePlay(false);
    this._updateStatsLive();
  }

  start(){
    const min = Number(this.el.min.value);
    const max = Number(this.el.max.value);
    const attemptLimit = this.el.limitChk.checked ? Number(this.el.limitInput.value) : null;
    try{
      this.game.start({ min, max, attemptLimit });
      this.el.log.innerHTML = '';
      this._setStatus(`Игра началась! Диапазон: ${min}…${max}. Сделайте предположение.`);
      this._enablePlay(true);
      this.el.guessInput.value = '';
      this.el.guessInput.focus();
      this.el.playAgainBtn.disabled = true;
      this._startTimer();
      this._updateStatsLive();
    }catch(err){
      this._setStatus(err.message || String(err));
    }
  }

  reset(){
    this.game.reset();
    this.el.log.innerHTML = '';
    this._enablePlay(false);
    this._stopTimer();
    this.el.statAttempts.textContent = '0';
    this.el.statElapsed.textContent = '0 сек.';
    this.el.statResult.textContent = '—';
    this.el.playAgainBtn.disabled = true;
    this._setStatus('Сброшено. Нажмите «Начать новую игру»');
  }

  submitGuess(){
    const val = Number(this.el.guessInput.value);
    try{
      const res = this.game.guess(val);
      const n = this.game.attempts;
      let msg = `#${n}: ${val} — `;
      if (res === 'low') msg += 'мало ↑';
      else if (res === 'high') msg += 'много ↓';
      else msg += 'угадано ✅';
      this._logLine(msg);

      if (res === 'correct'){
        this._finish(true);
      } else if (this.game.attemptLimit && this.game.attempts >= this.game.attemptLimit){
        this._finish(false);
      } else {
        this._setStatus(res === 'low' ? 'Загаданное число больше.' : 'Загаданное число меньше.');
        this.el.guessInput.select();
      }
      this._updateStatsLive();
    }catch(err){
      this._setStatus(err.message || String(err));
    }
  }

  _finish(win){
    this._enablePlay(false);
    this._stopTimer();
    this._setStatus(win
      ? `Победа! За ${this.game.attempts} попыток и ${formatDuration(this.game.durationMs)}.`
      : `Поражение. Лимит попыток исчерпан. Число было: ${this.game.target}.`
    );
    this.el.playAgainBtn.disabled = false;
  }

  _enablePlay(active){
    this.el.guessInput.disabled = !active;
    this.el.guessBtn.disabled = !active;
  }

  _setStatus(text){ this.el.status.textContent = text; }

  _logLine(text){
    const li = document.createElement('li');
    li.innerHTML = `<span>${text}</span><span class="time">${new Date().toLocaleTimeString()}</span>`;
    this.el.log.prepend(li);
  }

  _updateStatsLive(){
    this.el.statAttempts.textContent = this.game.attempts;
    const now = this.game.finishedAt ?? Date.now();
    const ms = now - (this.game.startedAt ?? now);
    this.el.statElapsed.textContent = formatDuration(ms);
    this.el.statResult.textContent = this.game.finishedAt ? (this.game.win ? 'Победа' : 'Поражение') : '—';
  }

  _startTimer(){
    this._stopTimer();
    this.timerId = setInterval(() => this._updateStatsLive(), 250);
  }

  _stopTimer(){
    if (this.timerId) clearInterval(this.timerId);
    this.timerId = null;
  }
}
