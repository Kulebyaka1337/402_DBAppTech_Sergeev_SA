import { randomInt } from './Utils.js';

export class Game {
  constructor(){
    this.reset();
  }

  reset(){
    this.min = 1;
    this.max = 100;
    this.target = null;
    this.attemptLimit = null; 
    this.guesses = [];        
    this.startedAt = null;
    this.finishedAt = null;
    this.durationMs = null;
    this.win = false;
  }

  /**
   * Старт новой партии
   * @param {{min:number,max:number,attemptLimit:number|null}} opts
   */
  start({ min=1, max=100, attemptLimit=null } = {}){
    if (typeof min !== 'number' || typeof max !== 'number' || min >= max){
      throw new Error('Недопустимый диапазон');
    }
    this.reset();
    this.min = Math.floor(min);
    this.max = Math.floor(max);
    this.attemptLimit = attemptLimit && attemptLimit > 0 ? Math.floor(attemptLimit) : null;
    this.target = randomInt(this.min, this.max);
    this.startedAt = Date.now();
  }

  /**
   * Сделать попытку
   * @param {number} value
   * @returns {'low'|'high'|'correct'}
   */
  guess(value){
    if (this.startedAt == null) throw new Error('Игра не начата');
    if (this.finishedAt != null) throw new Error('Игра уже завершена');
    if (typeof value !== 'number' || Number.isNaN(value)) throw new Error('Введите число');
    if (value < this.min || value > this.max) throw new Error(`Число вне диапазона (${this.min}..${this.max})`);

    let result = 'correct';
    if (value < this.target) result = 'low';
    else if (value > this.target) result = 'high';

    this.guesses.push({ value, result, ts: Date.now() });

    if (result === 'correct'){
      this._finish(true);
    } else if (this.attemptLimit && this.guesses.length >= this.attemptLimit){
      this._finish(false);
    }

    return result;
  }

  _finish(win){
    this.win = win;
    this.finishedAt = Date.now();
    this.durationMs = this.finishedAt - this.startedAt;
  }

  get attempts(){
    return this.guesses.length;
  }

  toJSON(){
    return {
      min: this.min, max: this.max, target: this.target,
      attempts: this.attempts, attemptLimit: this.attemptLimit,
      win: this.win, startedAt: this.startedAt, finishedAt: this.finishedAt, durationMs: this.durationMs,
      guesses: this.guesses
    };
  }
}
