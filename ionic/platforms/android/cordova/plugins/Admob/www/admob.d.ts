import Banner from './banner';
import Interstitial from './interstitial';
import RewardVideo from './reward-video';
import { BannerAd } from './banner';
declare class AdMob {
    banner: Banner;
    interstitial: Interstitial;
    rewardVideo: RewardVideo;
    private state;
    constructor();
    get BannerAd(): typeof BannerAd;
    setAppMuted(value: boolean): Promise<unknown>;
    setAppVolume(value: number): Promise<unknown>;
    setDevMode(value: boolean): void;
    private ready;
}
declare const admob: AdMob;
export default admob;
