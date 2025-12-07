
const apiKey    = 'AIzaSyC-EwtvF_rWHfDYm6fr_tcpn1NkI_wFMEs';
const channelId = 'UCzbu2l9o-a5Rh6Tbs9MIyHw';

async function fetchLatestVideos() {
    try {
        const url = new URL('https://www.googleapis.com/youtube/v3/search');
        url.searchParams.set('key', apiKey);
        url.searchParams.set('channelId', channelId);
        url.searchParams.set('order', 'date');
        url.searchParams.set('part', 'snippet');
        url.searchParams.set('type', 'video');
        url.searchParams.set('videoEmbeddable', 'true');
        url.searchParams.set('videoDuration', 'medium');
        url.searchParams.set('maxResults', '10');

        const response = await fetch(url.toString());
        const data     = await response.json();
        console.log('YouTube API response', data);

        if (data.items && data.items.length > 0) {
            const videoId = data.items[0].id.videoId;
            const iframe  = document.getElementById('latest-video');
            if (iframe) {
                iframe.src =
                    `https://www.youtube-nocookie.com/embed/${videoId}?rel=0&modestbranding=1`;
            }
        } else {
            console.error('No embeddable videos found.');
        }
    } catch (err) {
        console.error('Error fetching video:', err);
    }
}

document.addEventListener('DOMContentLoaded', fetchLatestVideos);
