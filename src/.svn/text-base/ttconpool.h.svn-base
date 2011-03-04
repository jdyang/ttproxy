#ifndef _GZK_TTCONPOOL_H_
#define _GZK_TTCONPOOL_H_

#include "thread_mutex.h"
#include <string>
#include <deque>
enum{
	NOCON = 0,
	OKCON = 1
};
using namespace std;

typedef struct
{
	int sock;
	int status;
}TTCON;

class TTconPool
{
	  public:
			enum{
				NOCON = 0,
				OKCON = 1
			};
	  public:
    	deque<TTCON*> m_deque;
    	Thread_Mutex m_CriticalSection;

    	char m_host[16];
    	int	 m_port;
    	int m_size;
    	int m_realsize;
    public:
    	TTconPool();
    	~TTconPool();

    	int Init(int size,string host,int port);
    	void UnInit();
    	TTCON* GetConnection();
    	void ReleaseConnection(TTCON* conn);
    	void TerminateConnection(TTCON* conn);
    	TTCON* CreateConnection();
    	int Reconnect(TTCON *conn);
    	void ReConnectAll();
};

  class TTCONGuard
  {
    public:
    	TTCONGuard(TTconPool *pool,TTCON *cnn)
    	{
    		m_pool = pool;
    		m_cnn = cnn;
    	}
    	void exchange(TTconPool *pool,TTCON *cnn)
    	{
    		m_pool->ReleaseConnection(m_cnn);
    		m_pool = pool;
    		m_cnn = cnn;
    	}
    	~TTCONGuard()
    	{
    		  
    			m_pool->ReleaseConnection(m_cnn);
    			if( TTconPool::NOCON == m_cnn->status ) m_pool->ReConnectAll();
    	}
    public:
    	TTconPool *m_pool;
    	TTCON *m_cnn;
  };
#endif
