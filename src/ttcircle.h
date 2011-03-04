#ifndef _GZK_CIRCLE_H_
#define _GZK_CIRCLE_H_
#include "chash.h"
#include "ttconpool.h"
#include <map>
#include "rwlock.h"
#include<list>
#include<stdint.h>
using namespace std;

class TTCircle
{
	public:
		TTCircle();
		~TTCircle();
		TTconPool * GetTTPool(string key);
		TTconPool * GetTTPoolOld(string key);
		bool add_server(string name,string ip,int port);
		bool del_server(string name);
		bool exist(string name);
		bool minus(list<string>& lname,string& name);
		bool Consist(){ return m_bconsist; }
		void finish_consist();
		map<string,TTconPool *> get_pool();
	public:
		map<string,pair<string,int> > m_server;
		map<string,TTconPool *> m_pool;
		ConsistHash m_hold;
		ConsistHash m_hnew;
		bool m_bconsist;
		RWLock m_rwlock;
		static int32_t TT_CONNECT_NUM;
};

#endif
