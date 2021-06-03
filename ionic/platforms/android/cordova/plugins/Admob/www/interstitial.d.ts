import { AdBase, TestIds } from './base';
import { IAdRequest } from '@admob-plus/core';
export default class Interstitial extends AdBase {
    protected testIdForAndroid: TestIds;
    protected testIdForIOS: TestIds;
    isLoaded(): Promise<unknown>;
    load(opts?: IAdRequest): Promise<void>;
    show(): Promise<unknown>;
}
