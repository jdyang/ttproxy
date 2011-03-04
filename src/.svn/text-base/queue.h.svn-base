#ifndef CPPSOCKET_QUEUE_H_
#define CPPSOCKET_QUEUE_H_

#include <pthread.h>
#include <list>

template<class T>
class SyncQueue
{
public:
	enum { DEFAULT_LIMIT = 1000 };

public:
	SyncQueue(size_t limit = DEFAULT_LIMIT) 
		: limit_(limit)
	{
		pthread_mutex_init(&mutex, NULL);
		pthread_cond_init(&cond, NULL);
	}

	virtual ~SyncQueue()
	{
		pthread_cond_destroy(&cond);
		pthread_mutex_destroy(&mutex);
	}

	virtual size_t GetSize() const
	{
		pthread_mutex_lock(&mutex);
		size_t size = queue.size();
		pthread_mutex_unlock(&mutex);
		return size;
	}

	int Push(const T& value)
	{
		pthread_mutex_lock(&mutex);

		if ( queue.size() > limit_ ) {
			pthread_mutex_unlock(&mutex);			
			return -1;
		}
			
		queue.push_back(value);

		pthread_cond_broadcast(&cond);
		pthread_mutex_unlock(&mutex);

		return 0;
	}

	int Pop(T& value, bool block)
	{
		pthread_mutex_lock(&mutex);
		while(true)
		{
			if (!queue.empty())
			{
				value = queue.front();
				queue.pop_front();
				pthread_mutex_unlock(&mutex);
				return 0;
			}

			if (!block)
			{
				pthread_mutex_unlock(&mutex);
				return -1;
			}

			pthread_cond_wait(&cond, &mutex);
		}
	}

private:

	mutable pthread_mutex_t mutex;
	mutable pthread_cond_t  cond;

	std::list<T> queue;
	size_t	limit_;	
	
};


#endif

