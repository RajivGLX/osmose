import { FormatageDatePipe } from './date-format.pipe';

describe('FormatageDatePipe', () => {
    it('create an instance', () => {
        const pipe = new FormatageDatePipe();
        expect(pipe).toBeTruthy();
    });
});