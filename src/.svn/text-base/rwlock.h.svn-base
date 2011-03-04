#ifndef CPPSOCKET_TREADWRITELOCK_H_
#define CPPSOCKET_TREADWRITELOCK_H_

#include <pthread.h>

  class RWLock
  {
  public:
  	pthread_rwlock_t rwlock;
  public:
  	RWLock();
  	~RWLock();
  	void ReadLock();
  	void WriteLock();
  	void Unlock();
  };

  class RLockGuard
  {
  public:
  	RWLock* m_rwlock;
  	RLockGuard(RWLock* rwlock)
  	{
  		m_rwlock = rwlock;
  		m_rwlock->ReadLock();
  	}
  	~RLockGuard()
  	{
  		m_rwlock->Unlock();
  	}
  };

  class WLockGuard
  {
  public:
  	RWLock* m_rwlock;
  	WLockGuard(RWLock* rwlock)
  	{
  		m_rwlock = rwlock;
  		m_rwlock->WriteLock();
  	}
  	~WLockGuard()
  	{
  		m_rwlock->Unlock();
  	}
  };

#endif

